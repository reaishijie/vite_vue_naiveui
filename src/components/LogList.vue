<script setup lang="ts" name="LogList">
import { ref, onMounted } from 'vue'
import { type ILogs } from '../types/logs'
import { records } from '../utils/auth'
import { useUserStore } from '../store/user'
import { useMessage, useDialog, NCard, NForm, NInput, NButton, NTable, NEl, NModal,NPagination,NTooltip,  NFormItem } from 'naive-ui'
const dialog = useDialog()
const message = useMessage()
const userStore = useUserStore()
const formRef = ref()
const logList = ref<ILogs[]>([])
const loading = ref(false)
const error = ref<string>('')
const page = ref(1)
const pageSize = ref(10)
const total = ref(0)
const allLogs = ref<ILogs[]>([]) // 存储所有数据
// const logList = ref<ILogs[]>([]) // 存储当前页显示的数据
records.interceptors.request.use(config => {
    const token = userStore.token
    if (token) {
        config.headers.Authorization = `Bearer ${token}`
    }
    return config
})
const getLogs = async () => {
    try {
        loading.value = true
        const response = await records.get('')

        // 保存所有数据并排序
        allLogs.value = response.data.data.sort((a: ILogs, b: ILogs) =>
            new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
        )

        // 更新总数
        total.value = allLogs.value.length

        // 更新当前页数据
        updatePageData()
    } catch (err: unknown) {
        // 检查是否是 401 错误
        const error = err as any
        if (error.response && error.response.status === 401) {
            message.error('登录已过期，自动退出登录')
            setTimeout(() => {
                userStore.logout()  // 退出登录
                window.location.reload() // 使用硬刷新
            }, 1000);
        } else {
            error.value = '获取日志失败'
        }
    } finally {
        loading.value = false
    }
}
// 更新当前页数据
const updatePageData = () => {
    const start = (page.value - 1) * pageSize.value
    const end = start + pageSize.value
    logList.value = allLogs.value.slice(start, end)
}

// 处理页码改变
const handlePageChange = (currentPage: number) => {
    page.value = currentPage
    updatePageData()
}

// 处理每页条数改变
const handlePageSizeChange = (size: number) => {
    pageSize.value = size
    page.value = 1  // 重置到第一页
    updatePageData()
}
//修改记录
const showEditModal = ref(false)//是否显示编辑框
const currentLog = ref<ILogs | null>(null) //当前记录的对象
const editContent = ref('')  //储存在编辑器中的内容
//点击修改按钮后触发changeLog事件,将L传给该函数
const changeLog = (L: ILogs) => {
    currentLog.value = L
    editContent.value = L.content
    showEditModal.value = true
}

// 取消编辑
const cancelEdit = () => {
    showEditModal.value = false
    editContent.value = ''
    currentLog.value = null
}

//提交修改触发事件
const confirmEdit = async () => {
    if (!currentLog.value) return
    try {
        loading.value = true
        await records.put(``, {
            id: currentLog.value.id,
            content: editContent.value
        })
        message.success('修改成功！')
        showEditModal.value = false
        await getLogs()
    } catch (error) {
        message.error('修改失败！')
    } finally {
        loading.value = false
    }
}
// 删除日志
const deleteLog = async (id: number) => {
    try {
        // 弹出确认对话框
        dialog.warning({
            title: '警告',
            content: '你确定要删除这条记录吗？',
            positiveText: '确定',
            negativeText: '取消',
            draggable: true,
            onPositiveClick: async () => {
                try {
                    loading.value = true
                    // 发送删除请求
                    await records.delete('', { data: { id: id } })
                    message.success('删除成功')
                    await getLogs() // 重新获取日志列表
                } catch (error: any) {
                    message.error(`删除失败: ${error.response?.data?.message || error.message}`)
                } finally {
                    loading.value = false
                }
            },
            onNegativeClick: () => {
                message.error('取消删除')
            }
        })
    } catch (error: any) {
        message.error('删除失败，请稍后重试')
    }
}

onMounted(() => {
    getLogs()
})
</script>

<template>
    <div class="log-list-container">
        <div v-if="loading" class="loading">
            <div class="loading-wave">
                <div class="loading-bar"></div>
                <div class="loading-bar"></div>
                <div class="loading-bar"></div>
                <div class="loading-bar"></div>
                <div class="loading-bar"></div>
            </div>
            <span class="loading-text">加载中...</span>
        </div>
        <div v-else-if="error" class="error">
            {{ error }}
        </div>
        <div v-else-if="logList.length === 0" class="empty">
            <n-card>暂无记录</n-card>
        </div>
        <div v-else class="table">
            <n-table>
                <thead>
                    <td >id</td>
                    <td>内容</td>
                    <td >时间</td>
                    <td style="width: 160px">操作</td>
                </thead>
                <tbody v-for="(L, index) in logList" :key="L.id">

                    <td>{{ total - ((page - 1) * pageSize + index) }}</td>
                    <td class="content-cell">
                        <n-tooltip trigger="hover" placement="top">
                            <template #trigger>
                                <span>{{ L.content.slice(0, 10) }}</span>
                            </template>
                            完整内容：{{ L.content }}
                        </n-tooltip>
                    </td>
                    <td>{{ L.created_at }}</td>
                    <td><!--L为当前点击的整个记录对象，包括id，user_id，content，time-->
                        <n-button type="info" @click="changeLog(L)">修改</n-button>
                        <n-button type="warning" @click="deleteLog(L.id)">删除</n-button>
                    </td>
                </tbody>
                <!--分页-->
                <n-el style="display: flex; margin-top: 30px; text-align: center; justify-content:left;">
                    <n-pagination v-model:page="page" v-model:page-size="pageSize" :item-count="total" show-size-picker
                        :page-sizes="[10, 20, 30, 40]" @update:page="handlePageChange"
                        @update:page-size="handlePageSizeChange" />
                </n-el>
            </n-table>

            <n-modal v-model:show="showEditModal">
                <n-card title="修改内容" style="width:40%;box-shadow: 0 0 8px skyblue;">
                    <n-form ref="formRef">
                        <n-form-item label="修改以下内容">
                            <n-input type="textarea" placeholder="在此输入内容" v-model:value="editContent"></n-input>
                        </n-form-item>
                    </n-form>
                    <template #action>
                        <n-button type="info" @click="cancelEdit">关闭</n-button>
                        <n-button type="primary" @click="confirmEdit">提交</n-button>
                    </template>
                </n-card>
            </n-modal>
        </div>
    </div>
</template>

<style scoped>
.n-button {
    padding: 10px;
    margin: 5px;
    box-shadow: 0 0 10px skyblue;
}

.loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 400px;
    z-index: 9999;
}

.loading-wave {
    display: flex;
    justify-content: center;
    align-items: flex-end;
    height: 50px;
    gap: 4px;
}
.loading-text {
    margin-top: 16px;
    color: #666;
    font-size: 14px;
    text-align: center;
    width: 100%;
    display: block;
}
.loading-bar {
    width: 8px;
    height: 20px;
    background: orange;
    border-radius: 4px;
    animation: wave 1s ease-in-out infinite;
}

.loading-bar:nth-child(2) {
    animation-delay: 0.1s;
}

.loading-bar:nth-child(3) {
    animation-delay: 0.2s;
}

.loading-bar:nth-child(4) {
    animation-delay: 0.3s;
}

.loading-bar:nth-child(5) {
    animation-delay: 0.4s;
}

.loading-text {
    margin-top: 16px;
    color: #666;
    font-size: 14px;
    text-align: center;
}

@keyframes wave {

    0%,
    100% {
        height: 20px;
    }

    50% {
        height: 40px;
    }
}

/* 修改表格样式 */
.table {
    width: 100%;
    overflow-x: auto;
}

.n-table {
    min-width: 600px;
    table-layout: fixed; /* 添加固定表格布局 */
}

.content-cell {
    padding-left: 8px; /* 添加左侧内边距 */
    max-width: 100%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

/* 适配移动端 */
@media screen and (max-width: 768px) {
    .n-button {
        padding: 6px;
        margin: 2px;
        font-size: 12px;
    }
    
    .content-cell {
        max-width: 150px;
    }
}
</style>