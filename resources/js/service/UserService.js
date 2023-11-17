import axios from 'axios';
import store from './../store.js';

export default class UserService {
    register(email) {
        return new Promise((resolve, reject) => {
            return axios.post('api/v1/register', {email : email})
                .then((response) => {
                    resolve(response.data);
                })
                .catch(function (error) {
                    if (error.response.data.errors != undefined) 
                        reject(error.response.data.errors);
                    else
                        reject(error.message);
                });
        });
    }
    
    registerConfirm(token, firstname, lastname, password, phone, firm_identifier) {
        var registerData = {
            firstname: firstname,
            lastname: lastname,
            password: password,
            phone: phone,
            firm_identifier: firm_identifier
        };
        
        return new Promise((resolve, reject) => {
            return axios.post('api/v1/register/confirm/' + token, registerData)
                .then((response) => {
                    resolve(response.data);
                })
                .catch(function (error) {
                    if (error.response.data.errors != undefined) 
                        reject(error.response.data.errors);
                    else
                        reject(error.message);
                });
        });
    }
    
    login(email, password) {
        var loginData = {
            email: email,
            password: password,
            device_name: 'vue-app'
        };
        
        return new Promise((resolve, reject) => {
            return axios.get('sanctum/csrf-cookie').then(() => {
                return axios.post('api/v1/login', loginData)
                    .then((response) => {
                        store.commit('setUserId', response.data.id);
                        resolve(response.data);
                    })
                    .catch(function (error) {
                        if (error.response.data.errors != undefined) 
                            reject(error.response.data.errors);
                        else
                            reject(error.message);
                    });
            });
        });
    }
    
    forgotPassword(email) {
        return new Promise((resolve, reject) => {
            return axios.post('api/v1/forgot-password', {email : email})
                .then((response) => {
                    resolve(response.data);
                })
                .catch(function (error) {
                    if (error.response.data.errors != undefined) 
                        reject(error.response.data.errors);
                    else
                        reject(error.message);
                });
        });
    }
    
    resetPassword(token, email, password) {
        var resetPasswordData = {
            token: token,
            email: email,
            password: password
        };
        
        return new Promise((resolve, reject) => {
            return axios.post('api/v1/reset-password', resetPasswordData)
                .then((response) => {
                    resolve(response.data);
                })
                .catch(function (error) {
                    if (error.response.data.errors != undefined) 
                        reject(error.response.data.errors);
                    else
                        reject(error.message);
                });
        });
    }
    
    logout() {
        axios.get('api/v1/logout')
            .then(() => {
                store.commit('setUserId', null);
            })
            .catch(function () {
                store.commit('setUserId', null);
            });
    }
    
    isLogin() {
        return new Promise((resolve, reject) => {
            return axios.get('api/v1/is-login')
                .then((response) => {
                    resolve(response);
                })
                .catch(function () {
                    store.commit('setUserId', null);
                    reject(false);
                });
        });
    }
}