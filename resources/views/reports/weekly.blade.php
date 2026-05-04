@extends('layouts.app')

@section('title', 'Weekly Report')

@section('content')
<div class="container">

    <h2 class="mb-4">📊 Daily Progress Report</h2>

    {{-- Chart --}}
    <div class="card mb-4">
        <div class="card-body">
            <canvas id="weeklyReportChart" height="120"></canvas>
        </div>
    </div>

    {{-- Table --}}
    <div class="card">
        <div class="card-body">

            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Completed</th>
                        <th>Total</th>
                        <th>Progress</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Prayer</td>
                        <td>{{ $weeklyData['prayer']['completed'] }}</td>
                        <td>{{ $weeklyData['prayer']['total'] }}</td>
                        <td>{{ $weeklyData['prayer']['percentage'] }}%</td>
                    </tr>

                    <tr>
                        <td>Fasting</td>
                        <td>{{ $weeklyData['fasting']['completed'] }}</td>
                        <td>{{ $weeklyData['fasting']['total'] }}</td>
                        <td>{{ $weeklyData['fasting']['percentage'] }}%</td>
                    </tr>

                    <tr>
                        <td>Quran</td>
                        <td>{{ $weeklyData['quran']['completed'] }}</td>
                        <td>{{ $weeklyData['quran']['total'] }}</td>
                        <td>{{ $weeklyData['quran']['percentage'] }}%</td>
                    </tr>

                    <tr>
                        <td>Charity</td>
                        <td>{{ $weeklyData['charity']['completed'] }}</td>
                        <td>{{ $weeklyData['charity']['total'] }}</td>
                        <td>{{ $weeklyData['charity']['percentage'] }}%</td>
                    </tr>
                </tbody>
            </table>

        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
    const labels = ['Prayer', 'Fasting', 'Quran', 'Charity'];

    const data = [
        {{ $weeklyData['prayer']['percentage'] }},
        {{ $weeklyData['fasting']['percentage'] }},
        {{ $weeklyData['quran']['percentage'] }},
        {{ $weeklyData['charity']['percentage'] }}
    ];

    new Chart(document.getElementById('weeklyReportChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Progress %',
                data: data,

                backgroundColor: [
                    '#1e6f5c',
                    '#2e8b57',
                    '#3cb371',
                    '#66cdaa'
                ],

                borderColor: '#145a4a',
                borderWidth: 2,

                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        color: '#1e6f5c'
                    }
                },
                x: {
                    ticks: {
                        color: '#1e6f5c'
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#1e6f5c'
                    }
                }
            }
        }
    });
</script>
@endpush