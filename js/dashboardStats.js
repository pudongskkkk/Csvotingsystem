// Function to fetch data from the server
async function fetchData(endpoint) {
    try {
        const response = await fetch(endpoint);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error(`Error fetching data from ${endpoint}:`, error);
        return { error: error.message };
    }
}

// Function to update the dashboard statistics
async function updateDashboardStats() {
    // Fetch candidate count
    const candidateData = await fetchData('../back_end/get_count.php?table=candidates');
    if (!candidateData.error) {
        document.getElementById('candidateCount').textContent = candidateData.count;
    } else {
        document.getElementById('candidateCount').textContent = 'Error';
    }

    // Fetch voter count
    const voterData = await fetchData('../back_end/get_count.php?table=approved_voters');
    if (!voterData.error) {
        document.getElementById('voterCount').textContent = voterData.count;
    } else {
        document.getElementById('voterCount').textContent = 'Error';
    }

    // Fetch voted count from vote_logs table
    const votedData = await fetchData('../back_end/get_voted_count.php');
    if (!votedData.error) {
        document.getElementById('votedCount').textContent = votedData.count;
    } else {
        document.getElementById('votedCount').textContent = 'Error';
    }
}

// Initialize when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', updateDashboardStats);

