import axios from 'axios';

export default class HistoryService {
    list(objectType, objectId, size, page, sort, order, search) {
        var data = {
            size: size,
            page: page,
            sort: sort,
            order: order,
            search: search,
        };
        return axios.get('api/v1/history/' + objectType + '/' + objectId, { params : data });
    }
}