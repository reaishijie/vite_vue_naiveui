<script setup lang="ts" name="LogList">
import { ref, onMounted } from 'vue'
import { type ILogs } from '../types/logs'
import { records } from '../utils/auth'
import { useUserStore } from '../store/user'
const { storeUserInfo } = useUserStore()
const token = ref(storeUserInfo?.token || '')
console.log(token);
const logList = ref<ILogs[]>([])
const loading = ref(false)
const error = ref('')
records.interceptors.request.use(config => {
    const token = useUserStore().token
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})
const getLogs = async () => {
    try {
        loading.value = true
        const response = records.get('')
        logList.value = (await response).data.data
     } catch (error) {
        console.error('获取日志失败:', error)
    } finally {
        loading.value = false
    }
}

const changeLog = () => {
    
}
const deleteLog = () => {
    
}
onMounted(() => {
    getLogs()
})
</script>

<template>
    <div class="log-list-container">
        <div v-if="loading" class="loading">
            加载中...
        </div>
        <div v-else-if="error" class="error">
            {{ error }}
        </div>
        <div v-else-if="logList.length === 0" class="empty">
            暂无记录
        </div>

        <div v-else class="table">
            <n-table>
                <thead>
                    <td>id</td>
                    <td>内容</td>
                    <td>时间</td>
                    <td colspan="2">操作</td>
                </thead>

                <tbody v-for="(L, index) in logList" :key="L.id">
                    <td>{{ logList.length - index }}</td>
                    <td>{{ L.content }}</td>
                    <td>{{ L.created_at }}</td>
                    <td>
                        <n-button type="info" @click="changeLog">修改</n-button>
                        <n-button type="warning" @click="deleteLog">删除</n-button>
                    </td>
                </tbody>
            </n-table>

        </div>
    </div>
</template>

<style scoped>
.n-button {
    padding: 10px;
    margin: 5px;
    box-shadow: 0 0 10px;
}
</style>
