import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener("DOMContentLoaded", () => {
    /*
    |--------------------------------------------------------------------------
    | Existing Mobile Sidebar
    |--------------------------------------------------------------------------
    */

    const sidebar = document.querySelector("[data-sidebar]");

    const sidebarBackdrop = document.querySelector("[data-sidebar-backdrop]");

    const sidebarOpen = document.querySelector("[data-sidebar-open]");

    const sidebarClose = document.querySelector("[data-sidebar-close]");

    const openSidebar = () => {
        if (!sidebar || !sidebarBackdrop) {
            return;
        }

        sidebar.classList.remove("-translate-x-full");

        sidebarBackdrop.classList.remove("hidden");

        requestAnimationFrame(() => {
            sidebarBackdrop.classList.remove("opacity-0");
        });

        document.body.classList.add("overflow-hidden");
    };

    const closeSidebar = () => {
        if (!sidebar || !sidebarBackdrop) {
            return;
        }

        sidebar.classList.add("-translate-x-full");

        sidebarBackdrop.classList.add("opacity-0");

        window.setTimeout(() => {
            sidebarBackdrop.classList.add("hidden");
        }, 200);

        document.body.classList.remove("overflow-hidden");
    };

    sidebarOpen?.addEventListener("click", openSidebar);

    sidebarClose?.addEventListener("click", closeSidebar);

    sidebarBackdrop?.addEventListener("click", closeSidebar);

    /*
    |--------------------------------------------------------------------------
    | Landing Smooth Scroll
    |--------------------------------------------------------------------------
    */

    document.querySelectorAll("[data-scroll-to]").forEach((button) => {
        button.addEventListener("click", () => {
            const selector = button.dataset.scrollTo;

            if (!selector) {
                return;
            }

            const target = document.querySelector(selector);

            target?.scrollIntoView({
                behavior: "smooth",
                block: "start",
            });
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Organization Switcher
    |--------------------------------------------------------------------------
    */

    const organizationTrigger = document.querySelector(
        "[data-organization-trigger]",
    );

    const organizationMenu = document.querySelector("[data-organization-menu]");

    organizationTrigger?.addEventListener("click", (event) => {
        event.stopPropagation();

        organizationMenu?.classList.toggle("hidden");
    });

    /*
    |--------------------------------------------------------------------------
    | Profile Menu
    |--------------------------------------------------------------------------
    */

    const profileTrigger = document.querySelector("[data-profile-trigger]");

    const profileMenu = document.querySelector("[data-profile-menu]");

    profileTrigger?.addEventListener("click", (event) => {
        event.stopPropagation();

        profileMenu?.classList.toggle("hidden");
    });

    /*
    |--------------------------------------------------------------------------
    | Close Dropdown
    |--------------------------------------------------------------------------
    */

    document.addEventListener("click", (event) => {
        if (
            organizationMenu &&
            organizationTrigger &&
            !organizationMenu.contains(event.target) &&
            !organizationTrigger.contains(event.target)
        ) {
            organizationMenu.classList.add("hidden");
        }

        if (
            profileMenu &&
            profileTrigger &&
            !profileMenu.contains(event.target) &&
            !profileTrigger.contains(event.target)
        ) {
            profileMenu.classList.add("hidden");
        }
    });

    /*
    |--------------------------------------------------------------------------
    | App Launcher
    |--------------------------------------------------------------------------
    */

    const searchInputs = [...document.querySelectorAll("[data-app-search]")];

    const appCards = [...document.querySelectorAll("[data-app-card]")];

    const categoryButtons = [
        ...document.querySelectorAll("[data-app-category]"),
    ];

    const stateButtons = [...document.querySelectorAll("[data-app-state]")];

    const emptySearch = document.querySelector("[data-app-empty-search]");

    let searchQuery = "";
    let activeCategory = "all";
    let activeState = "all";

    const refreshApps = () => {
        let visibleCount = 0;

        appCards.forEach((card) => {
            const name = (card.dataset.appName || "").toLowerCase();

            const category = (card.dataset.appCardCategory || "").toLowerCase();

            const state = (card.dataset.appStateValue || "").toLowerCase();

            const searchMatches =
                searchQuery === "" || name.includes(searchQuery);

            const categoryMatches =
                activeCategory === "all" || category === activeCategory;

            const stateMatches = activeState === "all" || state === activeState;

            const visible = searchMatches && categoryMatches && stateMatches;

            card.classList.toggle("hidden", !visible);

            if (visible) {
                visibleCount++;
            }
        });

        emptySearch?.classList.toggle("hidden", visibleCount > 0);
    };

    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    searchInputs.forEach((input) => {
        input.addEventListener("input", () => {
            searchQuery = input.value.trim().toLowerCase();

            searchInputs.forEach((otherInput) => {
                if (otherInput !== input) {
                    otherInput.value = input.value;
                }
            });

            refreshApps();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | State Filter
    |--------------------------------------------------------------------------
    */

    const setButtonActive = (buttons, selected) => {
        buttons.forEach((item) => {
            const active = item === selected;

            item.classList.toggle("bg-[#1d1d1b]", active);

            item.classList.toggle("text-white", active);

            item.classList.toggle("bg-white", !active);

            item.classList.toggle("text-[#65635d]", !active);
        });
    };

    stateButtons.forEach((button) => {
        button.addEventListener("click", () => {
            activeState = (button.dataset.appState || "all").toLowerCase();

            /*
             * State filter reset category.
             */
            activeCategory = "all";

            setButtonActive(stateButtons, button);

            categoryButtons.forEach((item) => {
                item.classList.remove("bg-[#1d1d1b]", "text-white");

                item.classList.add("bg-white", "text-[#65635d]");
            });

            refreshApps();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Category Filter
    |--------------------------------------------------------------------------
    */

    categoryButtons.forEach((button) => {
        button.addEventListener("click", () => {
            activeCategory = (
                button.dataset.appCategory || "all"
            ).toLowerCase();

            activeState = "all";

            setButtonActive(categoryButtons, button);

            stateButtons.forEach((item) => {
                const allButton = item.dataset.appState === "all";

                item.classList.toggle("bg-[#1d1d1b]", allButton);

                item.classList.toggle("text-white", allButton);

                item.classList.toggle("bg-white", !allButton);

                item.classList.toggle("text-[#65635d]", !allButton);
            });

            refreshApps();
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Locked Application Modal
    |--------------------------------------------------------------------------
    */

    const lockedModal = document.querySelector("[data-locked-app-modal]");

    const lockedName = lockedModal?.querySelector("[data-locked-app-name]");

    const lockedVersion = lockedModal?.querySelector(
        "[data-locked-app-version]",
    );

    const lockedCategory = lockedModal?.querySelector(
        "[data-locked-app-category]",
    );

    const lockedPrice = lockedModal?.querySelector("[data-locked-app-price]");

    const lockedPriceNote = lockedModal?.querySelector(
        "[data-locked-app-price-note]",
    );

    const money = (value) => {
        return new Intl.NumberFormat("id-ID").format(
            Math.round(Number(value || 0)),
        );
    };

    const closeLockedModal = () => {
        if (!lockedModal) {
            return;
        }

        lockedModal.classList.add("hidden");

        lockedModal.classList.remove("flex");

        document.body.classList.remove("overflow-hidden");
    };

    document.querySelectorAll("[data-locked-app]").forEach((button) => {
        button.addEventListener("click", () => {
            if (!lockedModal) {
                return;
            }

            const name = button.dataset.moduleName || "Application";

            const version = button.dataset.moduleVersion || "1.0.0";

            const category = button.dataset.moduleCategory || "Application";

            const pricingType = button.dataset.modulePricingType || "fixed";

            const fixedPrice = Number(button.dataset.moduleFixedPrice || 0);

            const perUser = Number(button.dataset.modulePricePerUser || 0);

            if (lockedName) {
                lockedName.textContent = name;
            }

            if (lockedVersion) {
                lockedVersion.textContent = `v${version}`;
            }

            if (lockedCategory) {
                lockedCategory.textContent = category;
            }

            if (lockedPrice) {
                if (pricingType === "included") {
                    lockedPrice.textContent = "Included in Plan";
                } else if (pricingType === "per_user") {
                    lockedPrice.textContent = `Rp ${money(perUser)}`;
                } else if (pricingType === "hybrid") {
                    lockedPrice.textContent = `Rp ${money(fixedPrice)} + Rp ${money(perUser)}`;
                } else if (fixedPrice > 0) {
                    lockedPrice.textContent = `Rp ${money(fixedPrice)}`;
                } else {
                    lockedPrice.textContent = "Contact Evognito";
                }
            }

            if (lockedPriceNote) {
                if (pricingType === "per_user") {
                    lockedPriceNote.textContent = "per user / billing period";
                } else if (pricingType === "hybrid") {
                    lockedPriceNote.textContent = "base fee + per user";
                } else if (pricingType === "included") {
                    lockedPriceNote.textContent =
                        "Contact administrator to activate";
                } else {
                    lockedPriceNote.textContent = "per billing period";
                }
            }

            lockedModal.classList.remove("hidden");

            lockedModal.classList.add("flex");

            document.body.classList.add("overflow-hidden");
        });
    });

    document.querySelectorAll("[data-locked-app-close]").forEach((button) => {
        button.addEventListener("click", closeLockedModal);
    });

    lockedModal?.addEventListener("click", (event) => {
        if (event.target === lockedModal) {
            closeLockedModal();
        }
    });

    /*
    |--------------------------------------------------------------------------
    | Escape
    |--------------------------------------------------------------------------
    */

    document.addEventListener("keydown", (event) => {
        if (event.key !== "Escape") {
            return;
        }

        closeSidebar();
        closeLockedModal();

        organizationMenu?.classList.add("hidden");

        profileMenu?.classList.add("hidden");
    });
});

Alpine.start();