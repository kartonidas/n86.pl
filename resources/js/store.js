import { defineStore } from 'pinia'

export const appStore = defineStore('store', {
    state: () => {
        return {
            userId: null,
            isOwner: false,
            toastMessage: null,
            permissions: null,
            tableOrder: {},
        };
    },
    actions: {
        setUserId (id) {
            this.userId = id;
        },
        
        setIsOwner (state) {
            this.isOwner = state;
        },
        
        setUserPermission (permissions) {
            this.permissions = permissions;
        },
        
        setToastMessage (data) {
            this.toastMessage = data;
        },
        
        setTableOrder (table, column, order) {
            if (this.tableOrder[table] == undefined)
                this.tableOrder[table] = {};
            
            this.tableOrder[table] = {
                'col' : column,
                'dir' : order,
            };
        },
        
        getTableOrder(table) {
            return this.tableOrder[table];
        },
    },
    persist: true,
})
