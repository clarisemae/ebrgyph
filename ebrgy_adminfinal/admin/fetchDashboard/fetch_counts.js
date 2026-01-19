// Initialize chartData to store document counts for the bar chart
let chartData = {
    barangay_certificate: 0,
    certificate_of_indigency: 0,
    certificate_of_comelec_registration: 0,
    certificate_of_non_residency: 0,
    //certificate_of_national_id: 0
};

// Initialize incidentData to store counts for the incident type pie chart
let incidentData = {};

// Define links for each document type
const documentLinks = {
    "Barangay Certificate": "admin_barangay_certificate.php",
    "Certificate of Indigency": "admin_certificate_of_indigency.php",
    "COMELEC Registration": "admin_certificate_of_comelec_registration.php",
    "Non-Residency": "admin_certificate_of_non_residency.php",
    // "National ID": "admin_certificate_of_national_id.php"
};


async function fetchCounts() {
    try {
        const urls = [
            'fetchDashboard/fetch_incident.php',
            'fetchDashboard/fetch_brgycert.php',
            'fetchDashboard/fetch_indigency.php',
            'fetchDashboard/registration.php',
            'fetchDashboard/non_residency.php',
            'fetchDashboard/national_id.php',
            'fetchDashboard/fetch_residents.php',
            'fetchDashboard/blotter.php',
            'fetchDashboard/announcement.php',
            'fetchDashboard/insights.php'
        ];

        for (const url of urls) {
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                console.log(`Response from ${url}:`, data);

                // Update counts for residents, incidents, announcements, and insights
                if (data.resident !== undefined) {
                    document.getElementById('resident-count').textContent = data.resident;
                }
                if (data.incident_report !== undefined) {
                    document.getElementById('incident_report-count').textContent = data.incident_report;
                }
                if (data.announcement !== undefined) {
                    document.getElementById('announcement-count').textContent = data.announcement;
                }
                if (data.insights !== undefined) {
                    document.getElementById('insights-count').textContent = data.insights;
                }

                // Update chartData for document requests
                if (data.barangay_certificate !== undefined) {
                    chartData.barangay_certificate = data.barangay_certificate;
                }
                if (data.certificate_of_indigency !== undefined) {
                    chartData.certificate_of_indigency = data.certificate_of_indigency;
                }
                if (data.certificate_of_comelec_registration !== undefined) {
                    chartData.certificate_of_comelec_registration = data.certificate_of_comelec_registration;
                }
                if (data.certificate_of_non_residency !== undefined) {
                    chartData.certificate_of_non_residency = data.certificate_of_non_residency;
                }
                if (data.certificate_of_national_id !== undefined) {
                    chartData.certificate_of_national_id = data.certificate_of_national_id;
                }

                // Update incidentData for incident types
                if (url.includes('fetch_incident.php') && Object.keys(data).length) {
                    incidentData = data;
                }
            } catch (error) {
                console.error(`Error fetching data from ${url}:`, error);
            }
        }

        // After fetching all data, render the charts
        renderDocumentRequestChart();
        renderIncidentTypeChart();
    } catch (error) {
        console.error('Error fetching counts:', error);
    }
}

function renderDocumentRequestChart() {
    const ctx = document.getElementById('document-request-chart').getContext('2d');

    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: [
                'Barangay Certificate',
                'Certificate of Indigency',
                'COMELEC Registration',
                'Non-Residency',
                'National ID'
            ],
            datasets: [{
                label: 'Document Requests',
                data: [
                    chartData.barangay_certificate,
                    chartData.certificate_of_indigency,
                    chartData.certificate_of_comelec_registration,
                    chartData.certificate_of_non_residency,
                    chartData.certificate_of_national_id
                ],
                backgroundColor: [
                    '#007bff',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6c757d'
                ],
                borderColor: [
                    '#0056b3',
                    '#1e7e34',
                    '#e0a800',
                    '#bd2130',
                    '#343a40'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            onClick: function(event, elements) {
                if (elements.length > 0) {
                    var firstElement = elements[0];
                    var label = this.data.labels[firstElement.index];
                    var url = documentLinks[label];
                    if (url) {
                        window.location.href = url;  // Redirect to the linked PHP page
                    }
                }
            },
            onHover: (event, chartElement) => {
                const target = event.native ? event.native.target : event.target;
                target.style.cursor = chartElement[0] ? 'pointer' : 'default';
            }
        }
    });
}



function renderIncidentTypeChart() {
    const ctx = document.getElementById('incident-type-chart').getContext('2d');

    const labels = Object.keys(incidentData); // Incident types
    const values = Object.values(incidentData); // Counts

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                label: 'Incident Types',
                data: values,
                backgroundColor: [
                    '#FF6384',
                    '#36A2EB',
                    '#FFCE56',
                    '#4BC0C0',
                    '#9966FF',
                    '#6c757d'
                ],
                borderColor: '#fff',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                datalabels: {
                    anchor: 'end', // Anchors the label at the end of the pie slice
                    align: 'end', // Aligns the label towards the outer edge of the chart
                    offset: -25, // Negative value to bring the labels inside the circle a bit
                    formatter: function (value) {
                        return value; // Displays the number
                    },
                    font: {
                        size: 12,
                        weight: 'bold'
                    },
                    color: '#606060', // Text color
                }
            }
        },
        plugins: [ChartDataLabels]
    });
}


// Call fetchCounts when the page loads
document.addEventListener('DOMContentLoaded', fetchCounts);
