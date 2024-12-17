const expandables = document.querySelectorAll('.expandable');

expandables.forEach(function(expandable) {
    const lines = 5;
    expandable.style.maxHeight = "calc(1.5em * " + lines + ")";
    expandable.style.overflow = "hidden";
    if (hasOverflow(expandable, lines)) {
        addExpandButton(expandable);
    }
});

function hasOverflow(element, lines) {
    if (countBRTags(element) > lines) {
        return true;
    }
    return false;
}

function addExpandButton(expandable) {
    var aTag = document.createElement('a');
    aTag.href = "#"
    aTag.addEventListener('click', function(event) {
        event.preventDefault();
        aTag.style.display = "none";
        expandable.style.maxHeight = "inherit";
    });
    aTag.innerHTML = 'Alle Versionen anzeigen';
    expandable.insertAdjacentElement('afterend', aTag);
}

function countBRTags(element) {
    var brTags = element.innerHTML.match(/<br>/g);
    return brTags ? brTags.length : 0;
}
