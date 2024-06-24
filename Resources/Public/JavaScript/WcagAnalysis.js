class WcagAnalysis {
    constructor() {
        this.initialize();
    }

    initialize() {
        document.addEventListener('DOMContentLoaded', () => {
            console.log('WCAG Plugin Loaded')
            const analyzeButton = document.getElementById('perform-wcag-analysis');
            if (analyzeButton) {
                analyzeButton.addEventListener('click', (e) => {
                    console.log('analyse')
                    e.preventDefault();
                    const pageUid = analyzeButton.dataset.pageUid;
                    this.performAnalysis(pageUid);
                });
            }
        });
    }

    performAnalysis(pageUid) {
        console.log(pageUid)
        fetch(TYPO3.settings.ajaxUrls['exdeliver_wcag_analyze'], {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `pageUid=${pageUid}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    this.updateAnalysisResults(data.results);
                } else {
                    alert('Analysis failed: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while performing the analysis.');
            });
    }

    updateAnalysisResults(results) {
        const resultsContainer = document.getElementById('wcag-analysis-results');
        if (resultsContainer) {
            resultsContainer.innerHTML = results;
        }
    }
}

export default new WcagAnalysis();