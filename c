#!/usr/bin/env python
#-*-coding:utf-8-*-
import sys
reload(sys)
sys.setdefaultencoding('utf-8')
import SocketServer, threading, json
from BaseHTTPServer import *
import subprocess
import os,time,datetime
import logging


class Logout():
    logger = None
    dir_name = 'log/'
    file_name = ''
    log_type = 'main'
    def __init__(self,file_name=None,log_type=None):
        if log_type != None : self.log_type = log_type
        self.init(file_name)
        self.log()

    def timeToStr(self,time_stamp=None,format='%Y-%m-%d %H:%M:%S'):
        if time_stamp==None:
            return time.strftime(format,time.localtime(time.time()))
        else:
            return time.strftime(format,time.localtime(time_stamp))

    def init(self,file_name):
        if not os.path.exists(self.dir_name):
            os.mkdir(self.dir_name)

        if file_name == None:
            self.file_name = str(self.timeToStr(time_stamp=None,format='%Y-%m-%d'))
        else:
            self.file_name = file_name
        pass

    def log(self):
        self.logger = logging.getLogger(self.log_type)
        self.logger.setLevel(logging.DEBUG)

        # 创建一个handler，用于写入日志文件
        file_path = self.dir_name+self.file_name+".txt"
        fh = logging.FileHandler(file_path)
        fh.setLevel(logging.DEBUG)

        # 定义handler的输出格式
        formatter = logging.Formatter('%(asctime)s - %(name)s - %(levelname)s - %(message)s')
        fh.setFormatter(formatter)

        # 给logger添加handler
        self.logger.addHandler(fh)
        pass

    def info(self,msg):
        nowDay = str(self.timeToStr(time_stamp=None,format='%Y-%m-%d'))
        if nowDay != self.file_name :
            self.init(nowDay)
            self.log()
        self.logger.info(msg)
        print msg
        pass


Log = Logout()

task_project = []

def kill_proc(proc):
    os.kill(proc.pid,9)

class Handlerx(BaseHTTPRequestHandler):

    def send(self,msg):
        global Log
        msg = json.dumps(msg)
        self.send_response(200)
        self.send_header("Content-type", "text/html")
        self.end_headers()
        self.wfile.write(msg)
        self.wfile.close()
        Log.info(msg)

    def do_POST(self):
        global task_project,Log
        if self.path=="/cmd":

            cur_thread = threading.currentThread()
            thread_name = cur_thread.name
            Log.info("---start---"+thread_name+"----")
            Log.info(self.client_address)
            Log.info(self.request_version)
            #获取参数
            size = 0
            try:
                if 'content-length' in self.headers :
                    size = int(self.headers['content-length'])
            except Exception, e:
                msg = {'status':'0','msg':unicode('参数错误',errors='ignore').encode('utf-8')}
                self.send(msg)
                return
            data = self.rfile.read(size)
            Log.info(data)

            try:
                user_config = json.loads(data)
                task_id = user_config['id']
                task_type = int(user_config['type'])
                cmd = user_config['cmd'].strip()
                time_out = int(user_config['timeout'])
                task_out = int(user_config['return'])
            except Exception, e:
                msg = {'status':'0','msg':unicode('参数错误',errors='ignore').encode('utf-8')}
                self.send(msg)
                return

            #0：单任务 
            if task_type==0: 
                if task_id in task_project:

                    msg = {'status':'0','msg':unicode('上次还没执行完',errors='ignore').encode('utf-8')}
                    self.send(msg)
                    return
                else :
                    task_project.append(task_id) 

            try:
                p = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE, stderr=subprocess.STDOUT)
            except Exception, e:

                msg = {'status':'0','msg':'error: '+str(e.message)}
                self.send(msg)
                return
            
            if task_out==0:
                msg = {'status':'1','msg':'success'}
                self.send(msg)

            timer = threading.Timer(int(time_out),kill_proc,[p])
            timer.start()

            (stdoutput,erroutput) = p.communicate()
            a = p.returncode

            timer.cancel()

            if stdoutput==None : stdoutput = ''
            if erroutput==None : erroutput = ''

            if task_type==0: 
                if task_id in task_project:
                    task_project.remove(task_id)

            try:
                erroutput = unicode( erroutput , errors='ignore').encode('utf-8','replace')
                stdoutput = unicode( stdoutput , errors='ignore').encode('utf-8','replace')

                if task_out==1:
                    msg = {'status':'1','msg':'success','out':str(stdoutput),'err':str(erroutput)}
                    self.send(msg)

            except Exception, e:

                msg = {'status':'1','msg':unicode('编码有误',errors='ignore').encode('utf-8')}
                self.send(msg)
            
        Log.info("----end-----"+thread_name+"----")


class ThreadedTCPServer(SocketServer.ThreadingMixIn, SocketServer.TCPServer):
    allow_reuse_address = True
    pass

if __name__ == "__main__":
    HOST = 8088
    print 'listen: '+str(HOST)+' ...'
    server = ThreadedTCPServer(('', HOST), Handlerx)
    server_thread = threading.Thread(target=server.serve_forever)
    server_thread.setDaemon(True)
    server_thread.start()
    server.serve_forever()
    
    
    PID=`ps -ef | grep 'cond.py' | grep -v grep | awk '{print $2}'`

if [ ! -z "$PID" ]; then
 echo NO
else
 echo YES
 nohup python /usr/local/python/crond/crondService.py  >/dev/null 2>>_err.log &
fi

##---------说明---------##


提价地址

http://127.0.0.1:8088/cmd

参数

{"id":"123456","type":"1","cmd":"D:\\xamp\\php\\php.exe E:\\BDProject\\test\\ind
ex.php","timeout":"10","return":"1"}


id : 编号
type : 类型	（0：单任务 1：普通任务）  

单任务 ：该任务上次没有运行完，不会运行该次提交
普通任务 : 不管该任务是否执行完，都运行

cmd ：运行的命令

timeout : 运行时间

return : 是否返回结果 （0：不返回 1：返回）


返回

status ：状态 (0：没有运行 1：运行)
msg : 系统信息 
err ：命令运行异常信息
out ：命令运行输出信息
