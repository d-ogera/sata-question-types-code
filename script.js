document.getElementById('quizForm').onsubmit = function() {
    var checkboxes = document.querySelectorAll('input[type="checkbox"]');
    var checkedOne = Array.prototype.slice.call(checkboxes).some(x => x.checked);
    
    if (!checkedOne) {
        alert('Please select at least one option.');
        return false;
    }
};
