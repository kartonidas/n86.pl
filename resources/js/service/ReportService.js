import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class ReportService {
    itemReport(itemId, period) {
        var data = {
            period: period,
        };
        
        return axios.get('api/v1/report/chart/item/' + itemId, { params : removeNullValues(data) });
    }
    
    rentalReport(rentalId, period) {
        var data = {
            period: period,
        };
        
        return axios.get('api/v1/report/chart/rental/' + rentalId, { params : removeNullValues(data) });
    }
}