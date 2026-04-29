<div wire:ignore>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>

    <style>
        .driver-disabled {
            border: 2px solid red !important;
        }

        @keyframes driverjs-shake {

            0%,
            100% {
                transform: translateX(0);
            }

            25% {
                transform: translateX(-5px);
            }

            75% {
                transform: translateX(5px);
            }
        }

        .driverjs-shake {
            animation: driverjs-shake 0.2s ease-in-out 0s 2;
        }
    </style>
</div>

<script>
    (function() {
        const tourName = @json($name);

        const tourConfig = {
            steps: @json($steps),
            validators: @json($validators), // ✅ SAFE now
        };

        function withEngine(callback) {
            if (window.TourEngine && window.TourEngine.register) {
                callback();
                return;
            }
            setTimeout(() => withEngine(callback), 40);
        }

        withEngine(() => {
            window.TourEngine.register(tourName, tourConfig);
        });
    })();
</script>
