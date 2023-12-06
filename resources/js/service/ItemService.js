import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class ItemService {
    settings() {
        return axios.get('api/v1/items/settings');
    }
    
    list(size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/items', { params : data });
    }
    
    create(itemData) {
        return axios.put('api/v1/item', removeNullValues(itemData));
    }
    
    get(itemId) {
        return axios.get('api/v1/item/' + itemId);
    }
    
    update(itemId, itemData) {
        return axios.put('api/v1/item/' + itemId, removeNullValues(itemData));
    }
    
    remove(itemId) {
        return axios.delete('api/v1/item/' + itemId);
    }
    
    validate(itemData) {
        return axios.post('api/v1/item/validate', removeNullValues(itemData));
    }
}