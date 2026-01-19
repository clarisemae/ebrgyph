document.addEventListener("DOMContentLoaded", () => {
    // Bar Chart for Task Overview
    const barChartOptions = {
        chart: {
            type: "bar",
            height: 250,
            toolbar: {
                show: false,
            },
        },
        series: [
            {
                name: "Tasks",
                data: [36, 23, 23],
            },
        ],
        xaxis: {
            categories: ["Document Request", "Incident Report", "Message Request"],
        },
        colors: ["#3498db"],
    };

    // Pie Chart for Requested Documents
    const pieChartOptions = {
        chart: {
            type: "pie",
            height: 250,
        },
        series: [6, 7, 6, 7, 10], // Replace with actual counts for each document
        labels: [
            "Barangay Certificate",
            "Certificate of Indigency",
            "COMELEC Registration Certificate",
            "Certificate of Non-Residency",
            "Certificate of National ID"
        ],
        colors: ["#3498db", "#f1c40f", "#e84393", "#2ecc71", "#9b59b6"], // Unique colors for each section
    };

    // Render Pie Chart
    const pieChart = new ApexCharts(document.querySelector("#pie-chart"), pieChartOptions);
    pieChart.render();


    // Render Bar Chart
    const barChart = new ApexCharts(document.querySelector("#bar-chart"), barChartOptions);
    barChart.render();

});
