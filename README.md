INSTALAR CENTOS7 64Bits Minimal

Colar o codigo Abaixo no terminal



yum install -y nano wget git
mkdir mbilling
cd /mbilling
git clone https://github.com/contatodct/MagnusBilling-SIPTI.git
chmod +x install.sh
./install.sh
