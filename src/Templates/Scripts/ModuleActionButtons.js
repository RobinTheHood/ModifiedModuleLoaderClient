const moduleActionButtons = document.querySelectorAll('.module-action-button .btn-group');

moduleActionButtons.forEach(function(moduleActionButton) {
    const actions = moduleActionButton.querySelectorAll('a');
    actions.forEach(function(action) {
        action.addEventListener('click', function(event) {
            event.preventDefault();
            //disableModuleActionButton(buttonGroup);
            disableAllModuleActionButtons();
            showSpinner(moduleActionButton, action);
            setTimeout(function() {
                window.location.href = action.getAttribute('href');
            }, 500);
        });
    });
});

function disableAllModuleActionButtons()
{
    moduleActionButtons.forEach(function(moduleActionButton) {
        disableModuleActionButton(moduleActionButton);
    });
}

function disableModuleActionButton(moduleActionButton) {
    const mainButton = moduleActionButton.querySelector('.btn');
    
    if (mainButton) {
        mainButton.classList.add('disabled');
    }

    const dropdownButton = moduleActionButton.querySelector('.dropdown-toggle');
    if (dropdownButton) {
        dropdownButton.classList.add('disabled');
    }
}

function showSpinner(moduleActionButton, action)
{
    const mainButton = moduleActionButton.querySelector('.btn');

    if (mainButton) {
        const html =
            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> '
            + mainButton.innerHTML;
        mainButton.innerHTML = html;
    }
}
