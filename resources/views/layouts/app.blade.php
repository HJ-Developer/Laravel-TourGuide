<head>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css">
    <script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
    <style>
        .driver-disabled {
            /* opacity: 0.4 !important; */
            /* pointer-events: none !important; */
            border: 2px solid red !important;
        }

        @keyframes driverjs-shake {

            0%,
            100% {
                transform: translateX(0)
            }

            25% {
                transform: translateX(-5px)
            }

            75% {
                transform: translateX(5px)
            }
        }

        .driverjs-shake {
            animation: driverjs-shake 0.2s ease-in-out 0s 2;
        }
    </style>
</head>
<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main>
        {{ $slot }}
    </flux:main>
</x-layouts::app.sidebar>
