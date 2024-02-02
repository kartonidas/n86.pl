const PasswordAllowedCharacters = {
    Uppers: "QWERTYUIOPASDFGHJKLZXCVBNM",
    Lowers: "qwertyuiopasdfghjklzxcvbnm",
    Numbers: "1234567890",
    Symbols: "!@#$%^&*"
}

const getRandomCharFromString = (str) => str.charAt(Math.floor(Math.random() * str.length))
const generatePassword = (length = 8) => { // password will be @Param-length, default to 8, and have at least one upper, one lower, one number and one symbol
    let pwd = "";
    pwd += getRandomCharFromString(PasswordAllowedCharacters.Uppers); //pwd will have at least one upper
    pwd += getRandomCharFromString(PasswordAllowedCharacters.Lowers); //pwd will have at least one lower
    pwd += getRandomCharFromString(PasswordAllowedCharacters.Numbers); //pwd will have at least one number
    pwd += getRandomCharFromString(PasswordAllowedCharacters.Symbols);//pwd will have at least one symbolo
    for (let i = pwd.length; i < length; i++)
        pwd += getRandomCharFromString(Object.values(PasswordAllowedCharacters).join('')); //fill the rest of the pwd with random characters
    return pwd
}

function __(text) {
    if(LOCALE.hasOwnProperty(text) && LOCALE[text] != undefined && LOCALE[text] != "")
        text = LOCALE[text];
        
    return text;
}