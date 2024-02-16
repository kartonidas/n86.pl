import axios from 'axios';
import { removeNullValues } from './../utils/helper.js';

export default class ReportService {
    itemBalanceReport(itemId, period) {
        var data = {
            period: period,
        };
        
        return axios.get('api/v1/report/balance/item/' + itemId, { params : removeNullValues(data) });
    }
    
    itemProfitReport(itemId, period) {
        var data = {
            period: period,
        };
        
        return axios.get('api/v1/report/profit/item/' + itemId, { params : removeNullValues(data) });
    }
    
    rentalBalanceReport(rentalId, period) {
        var data = {
            period: period,
        };
        
        return axios.get('api/v1/report/balance/rental/' + rentalId, { params : removeNullValues(data) });
    }
    
    rentalProfitReport(rentalId, period) {
        var data = {
            period: period,
        };
        
        return axios.get('api/v1/report/profit/rental/' + rentalId, { params : removeNullValues(data) });
    }
    
    itemReport(itemId) {
        return axios.get('api/v1/report/item/' + itemId);
    }
}