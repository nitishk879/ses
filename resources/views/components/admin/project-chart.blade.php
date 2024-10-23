<div>
    <div class="w-100">
        <canvas id="myChart"></canvas>
    </div>

    @push('scripts')
        <script>
            const labels = @json($labels);
            const data = {
                labels: labels,
                datasets: [{
                    label: 'Our Projects by months',
                    data: @json($datasets),
                    backgroundColor: 'rgba(0,136,255,0.51)',
                    borderColor: 'rgba(0,136,255,0.51)',
                    borderWidth: 1
                }]
            };

            const config = {
                type: 'bar', // or 'line', 'pie', etc.
                data: data,
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            };

            const myChart = new Chart(
                document.getElementById('myChart'),
                config
            );
        </script>
    @endpush
</div>
