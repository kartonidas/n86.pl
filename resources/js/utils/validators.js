export const pesel = (value) => {
    value = value == undefined ? "" : value;
    value = value.trim();
    
    if(!value.length)
        return true;
    
    if(!value.match(/^[0-9]+$/) || value.length != 11)
        return false;
    
    let sum = 0;
    let weights = [1, 3, 7, 9, 1, 3, 7, 9, 1, 3, 1];
    
    for (let i = 0; i < value.length; i++)
        sum += parseInt(value[i]) * weights[i];
    
    return sum % 10 == 0;
};

export const nip = (value) => {
    value = value == undefined ? "" : value;
    
    value = value.trim();
    if(!value.length)
        return true;

    value = value.replaceAll(/-|\s/g, "");

    if(value.length != 10 || !value.match(/^[0-9]{10}$/))
        return false;

    let sum = 0;
    let weights = [6, 5, 7, 2, 3, 4, 5, 6, 7];
    for (let i = 0; i < 9; i++)
        sum += weights[i] * value[i];
    
    let ctrlNumb = ((sum % 11) == 10) ? 0 : sum % 11;
    
    if (ctrlNumb !== parseInt(value[9]))
        return false;
    
    return true;
};

export const regon = (value) => {
    value = value == undefined ? "" : value;
    
    value = value.trim();
    if(!value.length)
        return true;
    
    if(!value.match(/^[0-9]{9}$/))
        return false;

    let sum = 0;
    let weights = [8, 9, 2, 3, 4, 5, 6, 7];
    
    for (let i = 0; i < 8; i++)
        sum += weights[i] * value[i];
    
    let ctrlNumb = ((sum % 11) == 10) ? 0 : sum % 11;
    
    if (ctrlNumb !== parseInt(value[8]))
        return false;
    
    return true;
};
