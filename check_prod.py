import paramiko
import time
import sys

ip = '49.9.50.81'
user = 'administrator'
password = '@Es1mk0!032026'

ssh = paramiko.SSHClient()
ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())

try:
    ssh.connect(ip, username=user, password=password, timeout=10)
    print("Connected successfully!")
    
    # Request a PTY using invoke_shell for Windows OpenSSH
    shell = ssh.invoke_shell()
    time.sleep(2)
    
    # Send command
    shell.send('powershell -Command "Get-ChildItem -Path C:\\inetpub -Filter .env -Recurse -ErrorAction SilentlyContinue | Select-Object FullName"\r\n')
    time.sleep(3)
    
    # Read output
    output = shell.recv(65535).decode('utf-8')
    print("--- Output ---")
    print(output)
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")
