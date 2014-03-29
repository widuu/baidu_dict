
##百度词典查词采集器（PHP版本）

![采集样本](https://raw.github.com/widuu/baidu_dict/master/2.png)

自己写的百度词典dict.baidu.com 采集翻译用的，你自己也可以自己定制，其中包含几个文件：

 1. word_data.php   13.5W单词库

 2. dict.class.php  采集类

 3. dictdemo.php    简单的采集案例

###使用方法

 只要把文件放到你的指定的目录下即可，然后运行dictdemo.php可以查看效果，入库的流程请自己编写

 `dict.class.php`中有`array2string()`的方法，来把数组转化成字符串方便入库。




