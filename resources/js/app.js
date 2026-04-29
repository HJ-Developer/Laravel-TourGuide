import createTour, {
    getTour,
    registerTour,
    startTour,
    registerValidator,
    registerCoreValidators,
} from "./tour-engine";

window.TourEngine = {
    create: createTour,
    register: registerTour,
    get: getTour,
    start: startTour,
    registerValidator,
};
registerCoreValidators();

window.startTour = (name) => window.TourEngine.start(name);
