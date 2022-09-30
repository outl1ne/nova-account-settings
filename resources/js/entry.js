Nova.booting((Vue, router, store) => {
  Nova.inertia(
    "NovaAccountSettings",
    require("./views/AccountSettings").default
  );
});
