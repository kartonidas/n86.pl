import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class UsersService {
    list(size, page) {
        var data = {
            size: size,
            page: page
        };
        return axios.get('api/v1/users', { params : data });
    }
    
    create(userData) {
        return axios.put('api/v1/user', userData);
    }
    
    get(userId) {
        return axios.get('api/v1/user/' + userId);
    }
    
    update(userId, userData) {
        return axios.put('api/v1/user/' + userId, removeNullValues(userData));
    }
    
    remove(userId) {
        return axios.delete('api/v1/user/' + userId);
    }
}