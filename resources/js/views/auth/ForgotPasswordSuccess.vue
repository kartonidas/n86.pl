<script>
    import { ref } from 'vue'
    import { setMetaTitle } from '@/utils/helper'
    
    export default {
        setup() {
            setMetaTitle('meta.title.forgot_password_success');
        }
    }
</script>

<template>
    <div class="text-center">
        {{ $t('register.forgot_password_reset_link') }}
    </div>
</template>