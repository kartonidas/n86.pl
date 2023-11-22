import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class ItemService {
    list(size, page) {
        var data = {
            size: size,
            page: page
        };
        return axios.get('api/v1/items', { params : data });
    }
    
    create(active, name, street, house_no, apartment_no, city, zip) {
        var itemData = {
            active : active,
            name : name,
            street : street,
            house_no : house_no,
            apartment_no : apartment_no,
            city : city,
            zip : zip,
        };
        return axios.put('api/v1/item', itemData);
    }
    
    get(itemId) {
        return axios.get('api/v1/item/' + itemId);
    }
    
    update(itemId, active, name, street, house_no, apartment_no, city, zip) {
        var itemData = {
            active : active,
            name : name,
            street : street,
            house_no : house_no,
            apartment_no : apartment_no,
            city : city,
            zip : zip,
        };
        
        return axios.put('api/v1/item/' + itemId, removeNullValues(itemData));
    }
    
    remove(itemId) {
        return axios.delete('api/v1/item/' + itemId);
    }
}