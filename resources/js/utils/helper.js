import { appStore } from '@/store.js';
import { useI18n } from 'vue-i18n'
import { useHead } from 'unhead'
import AppValues from '@/data/values.json';
import Prices from '@/data/prices.json';
import moment from 'moment'

export const getLocale = () => {
    return 'pl';
};

export const removeNullValues = (data) => {
    let outData = {};
    Object.keys(data).map((key) => {
        if(data[key] !== null) {
            outData[key] = data[key];
        }
    });
    return outData;
};

let __PERMISSIONS = null;
export const hasAccess = (module) => {
    if (module == "owner")
        return appStore().isOwner
    
    if (__PERMISSIONS == null) {
        __PERMISSIONS = [];
        if (appStore().permissions != undefined && appStore().permissions) {
            appStore().permissions.split(';').forEach((item) => {
                let module = item.split(':')[0];
                let operations = (item.split(':')[1]).split(',');
                
                operations.forEach((op) => {
                    __PERMISSIONS.push(module + ":" + op);
                });
            });
        }
    }
    
    
    if (__PERMISSIONS.indexOf(module) !== -1)
        return true;
    return false;
};

export const getResponseErrors = (errors) => {
    let outErrors = [];
    if (errors.response.data.errors != undefined) {
        for (var i in errors.response.data.errors) {
            errors.response.data.errors[i].forEach((err) => {
                outErrors.push(err);
            });
        }
    } else {
        if(errors.response.data.error != undefined && errors.response.data.error)
            outErrors.push(errors.response.data.message);
        else {
            if(errors.response.data.message != undefined && errors.response.data.message)
                outErrors.push(errors.response.data.message);
        }
    }
    return outErrors;
};

export const setMetaTitle = (module) => {
    const { t } = useI18n();
    
    useHead({
        title: t(module) + " | " + t("META_APP_NAME")
    });
};

export const getValues = (module) => {
    try {
        let keys = module.split(".");
        return keys.reduce((a, c) => a[c], AppValues[getLocale()]);
    } catch (err) {}
};

export const getValueLabel = (module, value, key = 'name') => {
    var label = '-';
    getValues(module).forEach((elem) => {
        if (elem.id == value) {
            label = elem[key];
        }
    });
    return label;
};

export const getPrices = () => {
    return Prices;
};

export const p = (i, w1, w2, w3) => {
    if(i == 1) return w1;

    let div1 = i % 10;
    if(div1 <= 1 || div1 >= 5) return w3;

    let div2 = (i - div1) / 10 % 10;
    if(div2 == 1) return w3;

    return w2;
};

export const timeToDate = (value)  => {
    return moment.unix(value).format("YYYY-MM-DD");
};

export const getRentalRowColor = (status) => {
    switch (status) {
        case "termination":
        case "archive":
            return "bg-bluegray-50 text-gray-500";
        
        case "current":
            return "bg-green-50";
            
        case "waiting":
            return "bg-blue-50";
    }
};

export const getRentalBoxColor = (status) => {
    switch (status) {
        case "termination":
        case "archive":
            return "text-gray-400 border-gray-300";
        
        case "current":
            return "text-green-600 border-green-600";
            
        case "waiting":
            return "text-blue-300 border-blue-300";
    }
};

export const getItemRowColor = (mode) => {
    switch (mode) {
        case "archived":
            return "bg-gray-100 text-gray-400 archived-item";
    }
};

export const getDatesFromRange = (start, end, interval) => {
    var days = [];
    for (var m = moment(start); m.isSameOrBefore(end); m.add(1, interval))
        days.push(m.toDate());
    return days;
};
