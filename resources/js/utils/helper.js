export const removeNullValues = (data) => {
    let outData = {};
    Object.keys(data).map((key) => {
        if(data[key]) {
            outData[key] = data[key];
        }
    });
    return outData;
}