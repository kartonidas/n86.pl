import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class RentalService {
    list(size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search
        };
        return axios.get('api/v1/rentals', { params : removeNullValues(data) });
    }
    
    rent(tenant, item, rent) {
        var rentData = {
            tenant : removeNullValues(tenant),
            item : removeNullValues(item),
            rent : removeNullValues(rent)
        };
        
        return axios.put('api/v1/rental/rent', rentData);
    }
    
    get(rentalId) {
        return axios.get('api/v1/rental/' + rentalId);
    }
    
    validate(rentalData) {
        return axios.post('api/v1/rental/validate', removeNullValues(rentalData));
    }
    
    remove(rentalId) {
        return axios.delete('api/v1/rental/' + rentalId);
    }
    
    terminate(rentalId, terminateData) {
        return axios.post('api/v1/rental/' + rentalId + '/terminate', terminateData);
    }
    
    bills(rentalId) {
        return axios.get('api/v1/rental/' + rentalId + '/bills');
    }
}