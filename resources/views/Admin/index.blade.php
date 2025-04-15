<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <!-- Bawaan Breeze -->
            {{ __('Dashboard') }} 
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- welcome -->
                </div>
            </div>
        </div>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Chart.js Bar Chart Canvas-->
                <div class="bg-white p-6 shadow-md rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Bar Chart</h3>
                    <canvas id="chartjs-bar"></canvas>
                </div>

                <!-- ApexCharts Pie Chart Canvas-->
                <div class="bg-white p-6 shadow-md rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Pie Chart</h3>
                    <div id="apexcharts-pie"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Load Script -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

</x-app-layout>
