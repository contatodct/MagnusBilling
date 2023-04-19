INSTALAR CENTOS7 64Bits Minimal

Colar o codigo Abaixo no terminal



yum install -y nano wget git
git clone https://github.com/contatodct/MagnusBilling-SIPTI.git
mv MagnusBilling-SIPTI mbilling
cd mbilling
chmod +x install.sh
./install.sh
