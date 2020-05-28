!function (a) {
    "function" == typeof define && define.amd ? define(["jquery", "moment"], a) : a(jQuery, moment)
}(function (a, b) {
    (b.defineLocale || b.lang).call(b, "et", {
        months: "Jaanuar_Veebruar_Märts_Aprill_Mai_Juuni_Juuli_August_September_Oktoober_November_Detsember".split("_"),
        monthsShort: "Jan_Veb_Mär_Apr_Mai_Jun_Jul_Aug_Sep_Okt_Nov_Dets".split("_"),
        weekdays: "Pühapäev_Esmaspäev_Teisipäev_Kolmapäev_Neljapäev_Reede_Laupäev".split("_"),
        weekdaysShort: "Püh_Esm_Tei_Kol_Nel_Re_Lau".split("_"),
        weekdaysMin: "P_E_T_K_N_R_L".split("_"),
        longDateFormat: {
            LT: "H:mm",
            LTS: "LT:ss",
            L: "D.MM.YYYY",
            LL: "D MMMM YYYY",
            LLL: "D MMMM YYYY LT",
            LLLL: "dddd, D MMMM YYYY LT"
        },
        calendar: {
            sameDay: "[Täna] LT",
            nextDay: "[Homme] LT",
            nextWeek: "dddd [at] LT",
            lastDay: "[Eile at] LT",
            lastWeek: "[Viimane] dddd [at] LT",
            sameElse: "L"
        },
        relativeTime: {
            future: "in %s",
            past: "%s ago",
            s: "a few seconds",
            m: "a minute",
            mm: "%d minutes",
            h: "an hour",
            hh: "%d hours",
            d: "päev",
            dd: "%d Päeva",
            M: "Kuu",
            MM: "%d Kuud",
            y: "Aasta",
            yy: "%d Aastat"
        },
        ordinalParse: /\d{1,2}(st|nd|rd|th)/,
        ordinal: function (a) {
            var b = a % 10,
                    c = 1 === ~~(a % 100 / 10) ? "th" : 1 === b ? "st" : 2 === b ? "nd" : 3 === b ? "rd" : "th";
            return a + c
        },
        week: {
            dow: 1,
            doy: 4
        }
    }), a.fullCalendar.datepickerLang("et", "et", {
        closeText: "Tehtud",
        prevText: "Eelmine",
        nextText: "Järgmine",
        currentText: "Täna",
        MonthNames: ["Jaanuar", "Veebruar", "Märts", "Aprill", "Mai", "Juuni", "Juuli", "August", "September", "Oktoober", "November", "Detsember"],
        MonthNamesShort: ["Jan", "Veb", "Mär", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dets"],
        dayNames: ["Pühapäev", "Esmaspäev", "Teisipäev", "Kolmapäev", "Neljapäev", "Reede", "Laupäev"],
        dayNamesShort: ["Püh", "Esm", "Tei", "Kol", "Nel", "Re", "Lau"],
        dayNamesMin: ["P", "E", "T", "K", "N", "R", "L"],
        weekHeader: "Ndl",
        dateFormat: "dd/mm/yy",
        firstDay: 1,
        isRTL: !1,
        showMonthAfterYear: !1,
        yearSuffix: ""
    }), a.fullCalendar.lang("et", {
        buttonText: {
            month: "Kuu",
            week: "Nädal",
            day: "Päev",
            list: "Nimekiri"
        },
        allDayText: "Terve päev",
        eventLimitText: function (a) {
            return "+ rohkem " + a
        }
    })
});