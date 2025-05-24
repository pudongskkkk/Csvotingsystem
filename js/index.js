function fetchCandidateCount() {
            // Using Fetch API to get data from a PHP endpoint
            fetch('../back_end/get_candidate_count.php')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('candidateCount').textContent = data.count;
                })
                .catch(error => {
                    console.error('Error fetching candidate count:', error);
                    document.getElementById('candidateCount').textContent = 'Error';
                }
            );
        }

        // Call the function when the page loads
        document.addEventListener('DOMContentLoaded', fetchCandidateCount);