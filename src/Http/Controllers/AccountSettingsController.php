<?php

namespace Outl1ne\NovaAccountSettings\Http\Controllers;

use Laravel\Nova\Panel;
use Illuminate\Http\Request;
use Laravel\Nova\ResolvesFields;
use Illuminate\Routing\Controller;
use Laravel\Nova\Contracts\Resolvable;
use Laravel\Nova\Fields\FieldCollection;
use Illuminate\Support\Facades\Validator;
use Laravel\Nova\Http\Requests\NovaRequest;
use Outl1ne\NovaAccountSettings\NovaAccountSettings;
use Illuminate\Http\Resources\ConditionallyLoadsAttributes;

class AccountSettingsController extends Controller
{
    use ResolvesFields, ConditionallyLoadsAttributes;

    public function get(Request $request)
    {
        if (!NovaAccountSettings::canSeeSettings()) return $this->unauthorized();

        if (! $path = $request->get('path')) {
            throw new \Exception('Required "path" parameter missing.');
        }

        $label = NovaAccountSettings::getPageName($path);
        $fields = $this->assignToPanels($label, $this->availableFields($path));
        $panels = $this->panelsWithDefaultLabel($label, app(NovaRequest::class));
        $account = NovaAccountSettings::getAccount();

        $addResolveCallback = function (&$field) use ($account) {
            if (!empty($field->attribute)) {
                $setting = $account->{$field->attribute};
                $fakeResource = $this->makeFakeResource($field->attribute, $setting ?? '');
                $field->resolve($fakeResource);
            }

            if (!empty($field->meta['fields'])) {
                foreach ($field->meta['fields'] as $_field) {
                    $setting = $account->{$_field->attribute};
                    $fakeResource = $this->makeFakeResource($_field->attribute, $setting ?? null);
                    $_field->resolve($fakeResource);
                }
            }
        };

        $fields->each(function (&$field) use ($addResolveCallback) {
            $addResolveCallback($field);
        });

        return response()->json([
            'panels' => $panels,
            'fields' => $fields,
            'authorizations' => NovaAccountSettings::getAuthorizations(),
        ], 200);
    }

    public function save(NovaRequest $request)
    {
        if (!NovaAccountSettings::getAuthorizations('authorizedToUpdate')) return $this->unauthorized();

        if (! $path = $request->get('path')) {
            throw new \Exception('Required "path" parameter missing.');
        }

        $account = NovaAccountSettings::getAccount();
        $fields = $this->availableFields($path);

        // NovaDependencyContainer support
        $fields = $fields->map(function ($field) {
            if (!empty($field->attribute)) return $field;
            if (!empty($field->meta['fields'])) return $field->meta['fields'];
            return null;
        })->filter()->flatten();

        $rules = [];
        foreach ($fields as $field) {
            $fakeResource = $this->makeFakeResource($field->attribute, $account->{$field->attribute});
            $field->resolve($fakeResource, $field->attribute); // For nova-translatable support
            $rules = array_merge($rules, $field->getUpdateRules($request));
        }

        Validator::make($request->all(), $rules)->validate();

        $fields->whereInstanceOf(Resolvable::class)->each(function ($field) use ($request, $account) {
            if (empty($field->attribute)) return;
            if ($field->isReadonly(app(NovaRequest::class))) return;

            // For nova-translatable support
            if (!empty($field->meta['translatable']['original_attribute'])) $field->attribute = $field->meta['translatable']['original_attribute'];

            $tempResource =  new \stdClass;
            $field->fill($request, $tempResource);

            if (!property_exists($tempResource, $field->attribute)) return;

            $account->{$field->attribute} = $tempResource->{$field->attribute};
        });

        $account->save();

        if (config('nova-account-settings.reload_page_on_save', false) === true) {
            return response()->json(['reload' => true]);
        }

        return response('', 204);
    }

    protected function availableFields($path)
    {
        return (new FieldCollection($this->filter(NovaAccountSettings::getFields($path))))->authorized(request());
    }

    protected function fields(Request $request, $path)
    {
        return NovaAccountSettings::getFields($path);
    }

    protected function makeFakeResource(string $fieldName, $fieldValue)
    {
        $fakeResource = new \stdClass;
        $fakeResource->{$fieldName} = $fieldValue;
        return $fakeResource;
    }

    /**
     * Return the panels for this request with the default label.
     *
     * @param  string  $label
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @return array
     */
    protected function panelsWithDefaultLabel($label, NovaRequest $request)
    {
        $method = $this->fieldsMethod($request);

        return with(
            collect(array_values($this->{$method}($request, $request->get('path', 'general'))))->whereInstanceOf(Panel::class)->unique('name')->values(),
            function ($panels) use ($label) {
                return $panels->when($panels->where('name', $label)->isEmpty(), function ($panels) use ($label) {
                    return $panels->prepend((new Panel($label))->withToolbar());
                })->all();
            }
        );
    }

    protected function unauthorized()
    {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    protected function assignToPanels($label, FieldCollection $fields)
    {
        return $fields->map(function ($field) use ($label) {
            if (!$field->panel) $field->panel = $label;
            return $field;
        });
    }
}
