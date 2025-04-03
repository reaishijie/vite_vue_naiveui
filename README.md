### nodejs环境

### vue3+typescript

### naiveui组件库

### 插件pinia、router、pinia-plugin-persistedstate、@vicons/ionicons5

#### 开发

git clone ...

cd ...

npm i

npm run dev

#### 打包

npm run build

#### 运行

将dist目录文件打包上传到静态网站目录，记得添加伪静态规则

```nginx
 location / {
    try_files $uri $uri/ /index.html;
 }
```
