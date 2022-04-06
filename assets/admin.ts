let userInputs: NodeListOf<HTMLInputElement> = document.querySelectorAll('.js-target-card');

userInputs.forEach(userInput => {
    userInput.addEventListener('keyup', (e) => {
        if(e.code === 'Enter') {
            window.location.href = `/admin/load-info/${userInput.value}`;
        }
    })
})