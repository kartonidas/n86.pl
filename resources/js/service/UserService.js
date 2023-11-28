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
        axios.get('api/v1/logout')
            .then(() => {
                appStore().setUserId(null);
                appStore().setUserPermission(null);
            })
            .catch(function () {
                appStore().setUserId(null);
                appStore().setUserPermission(null);
            });
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
                    resolve(response);
                    appStore().setUserPermission(response.data.permission);
                })
                .catch(function () {
                    appStore().setUserId(null);
                    reject(false);
                });
        });
    }
}