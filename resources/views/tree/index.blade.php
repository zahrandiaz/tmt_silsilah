<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Pohon Silsilah') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900" style="overflow-x: auto;">

                    <div id="chart_div"></div>

                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        google.charts.load('current', {packages:['orgchart']});
        google.charts.setOnLoadCallback(drawChart);

        function drawChart() {
            var data = new google.visualization.DataTable();
            // Kolom harus: Node, Parent, Tooltip
            data.addColumn('string', 'Name');
            data.addColumn('string', 'Manager');
            data.addColumn('string', 'ToolTip');

            // Mengambil data dari controller
            let chartData = {!! $chartData !!};
            
            // Data dari controller sudah dalam format yang benar [Node, Parent, Tooltip]
            data.addRows(chartData);

            // Buat chart
            var chart = new google.visualization.OrgChart(document.getElementById('chart_div'));
            
            // Gambar chart
            chart.draw(data, {
                'allowHtml': true,
                'size': 'large',
            });
        }
    </script>

    <style>
    .org-chart-node {
        border: 2px solid #b4b4b4; /* Atur border default jika perlu */
    }
    .google-visualization-orgchart-node-medium {
        font-size: 1rem; /* Sesuaikan ukuran font jika perlu */
    }
    </style>
</x-app-layout>