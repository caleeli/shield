## Install docker

If you have an older version of docker, first, stop the Docker service and remove the current version of Docker:

```bash
sudo systemctl stop docker
sudo yum remove docker docker-client docker-client-latest docker-common docker-latest docker-latest-logrotate docker-logrotate docker-engine
```

Install Docker CE:

Next, set up the Docker CE repository:
```bash
sudo yum install -y yum-utils
sudo yum-config-manager --add-repo https://download.docker.com/linux/centos/docker-ce.repo
```

Install the latest version of Docker CE and containerd:

```bash
sudo yum install -y docker-ce docker-ce-cli containerd.io
```

Start and enable the Docker service:
```bash
sudo systemctl start docker
sudo systemctl enable docker
```

Verify the Docker installation:
Run the following command to check the Docker version and ensure it's running correctly:

```bash
docker --version
```
