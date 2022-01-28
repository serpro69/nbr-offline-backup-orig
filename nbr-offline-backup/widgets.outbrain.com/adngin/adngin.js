let locationParams = window.location;
let params;
let widget;

window.addEventListener('load', function () {
    HandleObWidget();
})
window.scrollTo(0, document.body.scrollHeight);

function disableScrolling() {
    let x = window.scrollX;
    let y = window.scrollY;
    window.onscroll = function () {
        window.scrollTo(x, y);
    };
}

function enableScrolling() {
    window.onscroll = function () { };
}

function ScrollTo(widgetElem) {
    widgetElem.scrollIntoView({ block: "center" });
    disableScrolling();
    window.setTimeout(enableScrolling, 1000);
}

function HandleObWidget() {

    let selector
    let isHighlight = false
    let widget = ""

    if (!locationParams || !locationParams.search.length) { return; }
    params = locationParams.search.split('&');
    params.forEach((param) => {
        if (param === "highlight=true") { isHighlight = true }
    })
    params.forEach((param) => {
        if ((widget === "") && (param.split('widget=')[1])) {
            widget = param.split('widget=')[1]
        } else {
            return;
        }
    })

    if (!widget) { return; }
    selector = `div.ob-widget.${widget}`;
    // ALTERNATIVE SELECTOR
    // let widgetElems = document.querySelectorAll("[data-widget-id='" + widget + "']"); 

    Action(selector, isHighlight)
}

function Action(selector, isHighlight) {
    let widgetElem = document.querySelector(selector);
    let WaitCounter = 0;
    let intervalTime = 300;

    let inter = setInterval(function () {
        WaitCounter++

        if (widgetElem && document.readyState === 'complete') {
            widgetElem.scrollIntoView({ block: "center" });

            if (isHighlight) {
                widgetElem.style.outline = "3px solid orange";
            }
            document.addEventListener("DOMContentLoaded", ScrollTo(widgetElem));
            clearInterval(inter);
            return;
        } else if (WaitCounter * intervalTime > 4000) {
            clearInterval(inter);
            return;
        }

        widgetElems = document.querySelector(selector);
    }, intervalTime);
}
