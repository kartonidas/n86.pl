<script setup>
    import { ref } from 'vue'
    import { useRouter } from 'vue-router'
    import UserService from '@/service/UserService';

    const userService = new UserService();
    userService.logout();
    
    const router = useRouter()
    router.push({name: 'signin'})
</script>

<template></template>