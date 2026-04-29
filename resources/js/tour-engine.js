const registeredTours = new Map();

/**
 * Validator registry with scopes:
 * - global: shared across app
 * - page: page-specific overrides
 */
const validatorRegistry = {
    global: new Map(),
    page: new Map(),
};

/* -------------------------------------------------------
 * VALIDATOR REGISTRATION
 * ----------------------------------------------------- */

export function registerValidator(name, fn, scope = "page") {
    if (typeof fn !== "function") {
        throw new Error(`Validator "${name}" must be a function`);
    }

    if (!validatorRegistry[scope] || (scope !== "global" && scope !== "page")) {
        throw new Error(`Invalid validator scope: ${scope}`);
    }

    validatorRegistry[scope].set(name, fn);
}

/**
 * Resolve validator with precedence:
 * page > global
 */
function resolveValidator(name) {
    return (
        validatorRegistry.page.get(name) || validatorRegistry.global.get(name)
    );
}

/**
 * Optional helper (safe version)
 */
export function hasValidator(name) {
    return (
        validatorRegistry.page.has(name) || validatorRegistry.global.has(name)
    );
}

/* -------------------------------------------------------
 * CORE VALIDATION
 * ----------------------------------------------------- */

async function isStepValid(step, validators) {
    if (!step.requiresValidation) return true;

    const config = validators[step.key];
    if (!config) return true;

    const validator = resolveValidator(config.type);

    if (!validator) {
        console.warn(`❌ Validator not found: ${config.type}`);
        return false; // IMPORTANT: fail safe
    }

    try {
        const result = validator(config);
        return result instanceof Promise ? await result : result;
    } catch (err) {
        console.error("Validator error:", err);
        return false;
    }
}

/* -------------------------------------------------------
 * TOUR ENGINE
 * ----------------------------------------------------- */

export default function createTour({ steps, validators = {} }) {
    let driverObj;
    let activeIndex = 0;
    let nextBtn;

    const elementCache = new Map();

    function getElement(selector) {
        if (!elementCache.has(selector)) {
            elementCache.set(selector, document.querySelector(selector));
        }
        return elementCache.get(selector);
    }

    async function toggleNextButton() {
        if (!nextBtn) {
            nextBtn = document.querySelector(".driver-popover-next-btn");
        }

        if (!nextBtn) return;

        const step = steps[activeIndex];

        // optimistic disable while async validation resolves
        nextBtn.classList.add("driver-disabled");

        const valid = await isStepValid(step, validators);

        nextBtn.classList.toggle("driver-disabled", !valid);
    }

    function shake() {
        const step = steps[activeIndex];
        const el = getElement(step.element);

        if (!el) return;

        el.classList.add("driverjs-shake");
        setTimeout(() => el.classList.remove("driverjs-shake"), 400);
    }

    function init() {
        driverObj = window.driver.js.driver({
            showProgress: true,
            smoothScroll: true,
            overlayClickBehavior: undefined,
            steps,

            onHighlighted: (el, step, { state }) => {
                activeIndex = state.activeIndex;
                nextBtn = null;
                requestAnimationFrame(toggleNextButton);
            },

            onNextClick: async () => {
                const valid = await isStepValid(steps[activeIndex], validators);

                if (valid) {
                    driverObj.moveNext();
                } else {
                    shake();
                }
            },
        });

        const inputHandler = () => toggleNextButton();

        document.addEventListener("input", inputHandler);

        if (window.Livewire) {
            Livewire.hook("morph.updated", () => {
                if (!driverObj?.isActive()) return;

                elementCache.clear();
                toggleNextButton();
            });
        }

        return {
            drive() {
                driverObj.drive();
            },
            isActive: () => driverObj?.isActive?.(),
            destroy() {
                document.removeEventListener("input", inputHandler);
                driverObj?.destroy();
            },
        };
    }

    return {
        start() {
            return init().drive();
        },
    };
}

/* -------------------------------------------------------
 * TOUR REGISTRY
 * ----------------------------------------------------- */

export function registerTour(name, config) {
    if (!name) throw new Error("Tour name is required");
    if (!config || !Array.isArray(config.steps)) {
        throw new Error("Tour config must include steps array");
    }

    registeredTours.set(name, config);
}

export function getTour(name) {
    const config = registeredTours.get(name);

    if (!config) {
        throw new Error(`No tour registered: ${name}`);
    }

    return createTour(config);
}

let activeTourInstance = null;

export function startTour(name) {
    if (activeTourInstance?.isActive?.()) return;

    const tour = getTour(name);
    activeTourInstance = tour;
    tour.start();
}

/* -------------------------------------------------------
 * CORE VALIDATORS (GLOBAL)
 * ----------------------------------------------------- */

export function registerCoreValidators() {
    registerValidator(
        "email",
        ({ selector }) => {
            const val = document.querySelector(selector)?.value || "";
            return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val);
        },
        "global",
    );

    registerValidator(
        "checked",
        ({ selector }) => {
            return document.querySelector(selector)?.checked === true;
        },
        "global",
    );

    registerValidator(
        "minLength",
        ({ selector, min }) => {
            const val = document.querySelector(selector)?.value || "";
            return val.length >= min;
        },
        "global",
    );
}
