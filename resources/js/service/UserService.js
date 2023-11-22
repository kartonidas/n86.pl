import axios from 'axios';
import store from './../store.js';
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
    
    login(email, password) {
        var loginData = {
            email: email,
            password: password,
            device_name: 'vue-app'
        };
        
        return new Promise((resolve, reject) => {
            return axios.get('sanctum/csrf-cookie').then(() => {
                return axios.post('api/v1/login', loginData)
                    .then(
                        (response) => {
                            store.commit('setUserId', response.data.id);
                            store.commit('setUserPermission', response.data.permission);
                            resolve(response.data);
                        },
                        (response) => {
                            reject(response);
                        }
                    );
            });
        });
    }
    
    forgotPassword(email) {
        return axios.post('api/v1/forgot-password', {email : email});
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
                store.commit('setUserId', null);
                store.commit('setUserPermission', null);
            })
            .catch(function () {
                store.commit('setUserId', null);
                store.commit('setUserPermission', null);
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
                })
                .catch(function () {
                    store.commit('setUserId', null);
                    reject(false);
                });
        });
    }
}