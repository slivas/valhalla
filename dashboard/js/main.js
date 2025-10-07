document.addEventListener('DOMContentLoaded', () => {

    //Tabs
    function openTab(tabName, buttonElement) {
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelectorAll('.tab-content[data-tab="' + tabName + '"]').forEach(content => {
            content.classList.add('active');
        });
        buttonElement.classList.add('active');

    }

    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(button => {
        button.addEventListener('click', function () {
            const tabName = this.getAttribute('data-tab');
            openTab(tabName, this);
        });
    });

    if (tabButtons.length > 0) {
        tabButtons[0].click();
    }

});