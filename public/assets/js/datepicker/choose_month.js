const dateFrom = document.querySelector("#date_from");

const datepickerOptions = {
    format: "YYYY-MM",
    controls: true,
    headers: true,
    rows: 8,
    text: {
        title: "Wybierz datę",
        cancel: "Cofnij",
        confirm: "Potwierdź",
        year: "Rok",
        month: "Miesiąc"
    }
}

let datePicker = new Picker(dateFrom, datepickerOptions);
