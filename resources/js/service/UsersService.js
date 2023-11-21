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
    
    create(firstname, lastname, email, phone, password) {
        var userData = {
            firstname : firstname,
            lastname : lastname,
            email : email,
            phone : phone,
            password : password
        };
        return axios.put('api/v1/user', userData);
    }
    
    get(userId) {
        return axios.get('api/v1/user/' + userId);
    }
    
    update(userId, firstname, lastname, email, phone, password, superuser, permission_id) {
        var userData = {
            firstname : firstname,
            lastname : lastname,
            email : email,
            phone : phone,
            password : password,
            superuser : superuser,
            user_permission_id : permission_id,
        };
        
        return axios.put('api/v1/user/' + userId, removeNullValues(userData));
    }
    
    remove(userId) {
        return axios.delete('api/v1/user/' + userId);
    }
}