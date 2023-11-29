import axios from 'axios';
import { appStore } from './../store.js';
import { removeNullValues } from './../utils/helper.js';

export default class UserService {
    register(email) {
        return axios.post('api/v1/register', {email : email});
    }
    
    registerConfirm(token, firstname, lastname, password, phone, firm_identifier) {
        var registerData = {
            firstname: firstname,
            lastname: lastname,
            password: password,
            phone: phone,
            firm_identifier: firm_identifier
        };
        
        return axios.post('api/v1/register/confirm/' + token, registerData);
    }
    
    preLogin(email) {
        return axios.get('api/v1/get-email-firm-ids?email=' + email);
    }        
    
    login(email, password, firm_id) {
        var loginData = {
            email: email,
            password: password,
            device_name: 'vue-app',
            firm_id: firm_id,
        };
        
        return new Promise((resolve, reject) => {
            return axios.get('sanctum/csrf-cookie').then(() => {
                return axios.post('api/v1/login', removeNullValues(loginData))
                    .then(
                        (response) => {
                            appStore().setUserId(response.data.id);
                            appStore().setIsOwner(response.data.owner);
                            appStore().setUserPermission(response.data.permission);
                            resolve(response.data);
                        },
                        (response) => {
                            reject(response);
                        }
                    );
            });
        });
    }
    
    forgotPassword(email, firm_id) {
        return axios.post('api/v1/forgot-password', {email : email, firm_id : firm_id});
    }
    
    resetPassword(token, email, password) {
        var resetPasswordData = {
            token: token,
            email: email,
            password: password
        };
        
        return axios.post('api/v1/reset-password', resetPasswordData);
    }
    
    logout() {
        return new Promise((resolve, reject) => {
            return axios.get('api/v1/logout')
                .then(() => {
                    appStore().setUserId(null);
                    appStore().setIsOwner(false);
                    appStore().setUserPermission(null);
                    resolve(true);
                })
                .catch(function () {
                    appStore().setUserId(null);
                    appStore().setIsOwner(false);
                    appStore().setUserPermission(null);
                    reject(false);
                });
        })
    }
    
    profile() {
        return axios.get('api/v1/profile');
    }
    
    profileUpdate(firstname, lastname, email, phone, password) {
        var profileData = {
            firstname: firstname,
            lastname: lastname,
            email: email,
            phone: phone,
            password: password,
        };
        
        return axios.put('api/v1/profile', removeNullValues(profileData));
    }
    
    isLogin() {
        return new Promise((resolve, reject) => {
            return axios.get('api/v1/is-login')
                .then((response) => {
                    appStore().setUserPermission(response.data.permission);
                    resolve(response);
                })
                .catch(function () {
                    appStore().setUserId(null);
                    appStore().setIsOwner(false);
                    reject(false);
                });
        });
    }
    
    firmData() {
        return axios.get('api/v1/firm-data');
    }
    
    updateFirmData(firmData) {
        return axios.post('api/v1/firm-data', removeNullValues(firmData));
    }
    
    invoiceData() {
        return axios.get('api/v1/invoice-data');
    }
    
    updateInvoiceData(invoiceData) {
        return axios.post('api/v1/invoice-data', removeNullValues(invoiceData));
    }
}