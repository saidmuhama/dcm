// document.addEventListener("DOMContentLoaded", function () {

//     const chartElement = document.getElementById("summarychart");

//     // Safety check
//     if (!chartElement) {
//         console.error("Chart canvas #summarychart not found");
//         return;
//     }

//     const ctx = chartElement.getContext("2d");

//     // Gradients
//     const gradientFees = ctx.createLinearGradient(0, 0, 0, 280);
//     gradientFees.addColorStop(0, "rgba(0, 73, 232, 1)");
//     gradientFees.addColorStop(0.5, "rgba(0, 168, 133, 0.5)");
//     gradientFees.addColorStop(1, "rgba(255, 193, 7, 0)");

//     const gradientTransactions = ctx.createLinearGradient(0, 0, 0, 280);
//     gradientTransactions.addColorStop(0, "rgba(3, 4, 94, 0.85)");
//     gradientTransactions.addColorStop(1, "rgba(0, 73, 232, 0)");

//     let summaryChart = null;

//     // 🔄 Load data from server
//     function loadChartData(url = "../ajax/ajax_chart_data.php") {

//         fetch(url)
//             .then(response => response.json())
//             .then(data => {

//                 // Destroy old chart if exists (important for reuse)
//                 if (summaryChart) {
//                     summaryChart.destroy();
//                 }

//                 summaryChart = new Chart(ctx, {
//                     type: "bar",
//                     data: {
//                         labels: data.labels || [],
//                         datasets: [
//                             {
//                                 label: "Fees Collected",
//                                 data: data.amounts || [],
//                                 borderRadius: 12,
//                                 backgroundColor: gradientFees,
//                                 borderWidth: 0
//                             },
//                             {
//                                 label: "Transactions",
//                                 data: data.transactions || [],
//                                 borderRadius: 12,
//                                 backgroundColor: gradientTransactions,
//                                 borderWidth: 0
//                             }
//                         ]
//                     },
//                     options: {
//                         responsive: true,
//                         maintainAspectRatio: false,

//                         interaction: {
//                             mode: "index",
//                             intersect: false
//                         },

//                         plugins: {
//                             legend: {
//                                 display: true,
//                                 position: "top"
//                             },
//                             tooltip: {
//                                 enabled: true
//                             }
//                         },

//                         scales: {
//                             x: {
//                                 grid: {
//                                     display: false
//                                 }
//                             },
//                             y: {
//                                 beginAtZero: true,
//                                 grid: {
//                                     color: "rgba(200,200,200,0.2)"
//                                 }
//                             }
//                         }
//                     }
//                 });

//             })
//             .catch(error => {
//                 console.error("Error loading chart data:", error);
//             });
//     }

//     // 🚀 Initial load
//     loadChartData();

//     // 🔄 OPTIONAL: Auto refresh every 10 seconds
//     // Comment this if you DON'T want auto reload
//     setInterval(() => {
//         loadChartData();
//     }, 10000);

//     // 🌟 GLOBAL FUNCTION (so you can reload manually anywhere)
//     window.reloadSummaryChart = function (customUrl = null) {
//         loadChartData(customUrl || "../ajax/ajax_chart_data.php");
//     };

// });