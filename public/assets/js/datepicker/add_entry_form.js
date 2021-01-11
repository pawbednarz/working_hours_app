const place = document.querySelector("#place");
const dateFrom = document.querySelector("#date_from");
const dateFromHour = document.querySelector("#time_from_hour");
const dateFromMinute = document.querySelector("#time_from_minute");
const dateTo = document.querySelector("#date_to");
const dateToHour = document.querySelector("#time_to_hour");
const dateToMinute = document.querySelector("#time_to_minute");


const datepicker_from = new Datepicker(dateFrom);
const datepicker_to = new Datepicker(dateTo)

const checkboxes = document.querySelectorAll("input[type=checkbox");

checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
        checkbox.checked ? checkbox.setAttribute("value", true) : checkbox.setAttribute("value", false)
    })
})

// TODO make form fields disabled, when user checks day_off checkbox
const dayOff = checkboxes[2];

dayOff.addEventListener("change", () => {
    if (dayOff.checked) {
        disableFormFields();
    } else {
        enableFormFields();
    }
})

function disableFormFields() {
    place.setAttribute("disabled", "");
    dateFromHour.setAttribute("disabled", "");
    dateFromMinute.setAttribute("disabled", "");
    dateTo.setAttribute("disabled", "");
    dateToHour.setAttribute("disabled", "");
    dateToMinute.setAttribute("disabled", "");
    // driver checkbox
    checkboxes[0].setAttribute("disabled", "");
    // subsistence allowance checkbox
    checkboxes[1].setAttribute("disabled", "");
}

function enableFormFields() {
    place.removeAttribute("disabled");
    dateFromHour.removeAttribute("disabled");
    dateFromMinute.removeAttribute("disabled");
    dateTo.removeAttribute("disabled");
    dateToHour.removeAttribute("disabled");
    dateToMinute.removeAttribute("disabled");
    // driver checkbox
    checkboxes[0].removeAttribute("disabled");
    // subsistence allowance checkbox
    checkboxes[1].removeAttribute("disabled");
}
