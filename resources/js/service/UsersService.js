import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class UsersService {
    list(meta) {
        return axios.get('api/v1/users', { params : removeNullValues(meta) });
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