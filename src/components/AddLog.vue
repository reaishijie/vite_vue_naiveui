<script setup lang="ts" name="AddLog">
import { ref, watch, reactive } from 'vue'
import type { FormInst } from 'naive-ui'
import { records } from '../utils/auth'
import { useUserStore } from '../store/user'
import { useMessage, NCard, NForm, NInput, NButton, NTimePicker, NFormItem } from 'naive-ui'
const message = useMessage()
const userStore = useUserStore()

records.interceptors.request.use(config => {
    const token = userStore.token
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})
const formData = reactive({
    editContent: '',
    editBeginTime: null,
    editEndTime: null,
    editLastTime: ''
})

const submit = async () => {
    try {
        if (!formData.editContent) {
            message.warning('请输入内容！')
            return;
        }
        // 构建提交数据
        const submitData = {
            content: formData.editContent,
            record_date: formData.editBeginTime
                ? new Date(formData.editBeginTime).toISOString().slice(0, 19).replace('T', ' ')
                : undefined
        };
        // 发送请求
        const response = await records.post('', submitData)
        if (response.data.code === 200) {
            message.success('添加记录成功！')
            formData.editContent = ''
        }

    } catch (error: any) {
        message.error('提交失败：' + (error.response?.data?.message || '未知错误'))
    }
}

const formRef = ref<FormInst | null>(null)
// const rules = {
//     editContent: {
//         required: true,
//         trigger: ['blur'],
//         validator: (_rule: any, value: string) => {
//             console.log('验证内容：', value)
//             if (!value) {
//                 console.log('内容为空')
//                 return new Error('请输入内容')
//             }
//             if (value.length < 10) {
//                 console.log('内容长度小于10')
//                 return new Error('不能小于10')
//             }
//             if (value.length > 20) {
//                 console.log('内容长度大于20')
//                 return new Error('不能大于20')
//             }
//             console.log('验证通过')
//             return true
//         }
//     },
//     editBeginTime: {
//         required: false,
//         trigger: ['blur'],
//     },
//     editEndTime: {
//         required: false,
//         trigger: ['blur']
//     },
//     editLastTime: {
//         required: true,
//         trigger: ['blur']
//     }
// }
watch(
    [() => formData.editBeginTime, () => formData.editEndTime],
    ([newBegin, newEnd]) => {
        if (!newBegin || !newEnd) {
            formData.editLastTime = ''
            return
        }
        const diffMinutes = Math.floor((newEnd - newBegin) / (1000 * 60))
        formData.editLastTime = `${diffMinutes}分钟`
    }
)
</script>

<template>
    <div class="container">
        <n-card>
            <div class="header">
                <h3>添加记录</h3>
            </div>
            <div class="body">
                <n-form ref="formRef">
                    <n-form-item label="内容" :model="formData" path="editContent">
                        <n-input placeholder="请输入记录的内容..." type="textarea" v-model:value="formData.editContent"
                            row="5"></n-input>
                    </n-form-item>
                    <n-form-item label="开始时间" :show-feedback="false" path="editBeginTime">
                        <n-time-picker v-model:value="formData.editBeginTime" style="width: 50%"
                            time-zone="Asia/Shanghai" placeholder="请选择时间" />
                    </n-form-item>
                    <n-form-item label="结束时间" :show-feedback="false" path="editEndTime">
                        <n-time-picker v-model:value="formData.editEndTime" style="width: 50%" time-zone="Asia/Shanghai"
                            placeholder="请选择时间" />
                    </n-form-item>
                    <n-form-item label="持续时间" path="editLastTime">
                        <n-input type="text" v-model:value="formData.editLastTime" style="width: 30%"></n-input>
                    </n-form-item>
                </n-form>
                <br>
                <n-button type="info" @click="submit">提交</n-button>
            </div>
        </n-card>
    </div>
</template>

<style scoped>
.container {
    display: flex;
    justify-content: center;
    background-color: #fff;
}

.n-card {
    width: 100%;
}

.body {
    width: 80%;
    padding: 10px;
    background-color: #fff;
    box-shadow: 0 0 10px skyblue;
}

.n-input {
    margin-bottom: 10px;
}
</style>