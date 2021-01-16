const place = document.querySelector("#place");
const dateFrom = document.querySelector("#date_from");
const dateFromHour = document.querySelector("#time_from_hour");
const dateFromMinute = document.querySelector("#time_from_minute");
const dateTo = document.querySelector("#date_to");
const dateToHour = document.querySelector("#time_to_hour");
const dateToMinute = document.querySelector("#time_to_minute");


const datepickerOptions = {
    "autohide": true,
    "disableTouchKeyboard": true,
    "todayHighlight": true,
    "todayBtn": true,
    "prevArrow": "<<<",
    "nextArrow": ">>>",
    "minDate": new Date(2015, 0),
    "maxDate": new Date(2100, 12),
    "weekStart": 1,
    "daysOfWeekHighlighted": [1,2,3,4,5]
}
const datepickerFrom = new Datepicker(dateFrom);
datepickerFrom.setOptions(datepickerOptions);

dateFrom.addEventListener("changeDate", function() {
    datepickerTo.setDate(datepickerFrom.getDate());
})

const datepickerTo = new Datepicker(dateTo)
datepickerTo.setOptions({
    "autohide": true,
    "disableTouchKeyboard": true
});

let today = new Date().toISOString().slice(0, 10)

datepickerFrom.setDate(today);
datepickerTo.setDate(today);

datepickerFrom.update(() => {
    datepickerTo.setDate("2020-01-01");
})

const checkboxes = document.querySelectorAll("input[type=checkbox");

checkboxes.forEach((checkbox) => {
    checkbox.addEventListener("change", () => {
        checkbox.checked ? checkbox.setAttribute("value", true) : checkbox.setAttribute("value", false)
    })
})

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
