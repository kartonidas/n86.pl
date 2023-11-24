import { appStore } from '@/store.js';

export const removeNullValues = (data) => {
    let outData = {};
    Object.keys(data).map((key) => {
        if(data[key] !== null) {
            outData[key] = data[key];
        }
    });
    return outData;
}

let __PERMISSIONS = null;
export const hasAccess = (module) => {
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
}

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
}