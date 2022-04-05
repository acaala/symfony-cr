
let tableRows = document.querySelectorAll('.js-table-row');


tableRows.forEach((row, i) => {
    if(i > 10) {
        row.classList.add('hidden');
        console.log(row.classList);
    }
})