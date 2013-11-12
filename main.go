package main

import (
	"archive/zip"
	"bufio"
	"bytes"
	"fmt"
	"github.com/santios/pila/common"
	"io"
	"log"
	"os"
)

var (
	Ruta = "testdata/"
)

func ValidarVariables() {
	if pilaDb := os.Getenv("PILADB"); pilaDb == "" {
		log.Fatal("Fatal: PILADB not found")
	}

	if pilaDb := os.Getenv("PILAUSER"); pilaDb == "" {
		log.Fatal("Fatal: PILAUSER not found")
	}

	if pilaDb := os.Getenv("PILAPASS"); pilaDb == "" {
		log.Fatal("Fatal: PILAPASS not found")
	}

	if pilaDb := os.Getenv("PILAHOST"); pilaDb == "" {
		log.Fatal("Fatal: PILAHOST not found")
	}

}

func Prueba() {
	ValidarVariables()
	r, err := zip.OpenReader(Ruta + "230201_2013-03-06_I.zip")
	if err != nil {
		log.Fatal(err)
	}
	defer r.Close()

	numtxts := 0

	containerArch := common.NewArchivos()
	// Iterate through the files in the archive,
	// printing some of their contents.
	for _, f := range r.File {

		//Returns a io.ReaderCloser
		rc, err := f.Open()
		if err != nil {
			log.Fatal(err)
		}
		//Here we create a buffer from the ReaderCloser
		buf := new(bytes.Buffer)
		buf.ReadFrom(rc)
		//Transform the *bytes.Buffer to []Bytes
		bytesArray := buf.Bytes()

		rc.Close()

		// Imprimimos datos del archivo principal
		//fmt.Printf("Contents of %s (size: %d)\n", f.Name, f.CompressedSize64)

		//Leemos los otros zip que hay dentro del archivo
		//@todo Como hacer para usar aqui la funcion ToReader. Dice que no implementa ReaderAt
		z, err := zip.NewReader(bytes.NewReader(bytesArray), int64(len(bytesArray)))

		if err != nil {
			log.Fatal(err)
			fmt.Printf("******* NOT A ZIP FILE MEN *******\n")
		}

		//Imprimimos datos del archivo zip interno
		for _, f2 := range z.File {

			arch := common.NewArchivo(f2.Name, f.Name)

			filelen := len(f2.Name)
			if descartarX(f2.Name, filelen) || descartarPensionados(f2, filelen) {
				continue
			}
			rc2, err := f2.Open()

			if err != nil {
				log.Fatal(err)
			}

			buf := new(bytes.Buffer)
			reader, _ := ToReader(buf, &rc2)

			scanner := bufio.NewScanner(reader)
			lineas := 0
			for scanner.Scan() {
				arch.AppendLinea([]byte(scanner.Text()))
				//@todo Verificar porque no puedo usar esta linea. Ocurre algo extraño. Parece
				//      que se juntaran vairas lienas. No se porqué. Por ahora, transformamos de
				//      nuevo a []byte mientras encontramos el error.
				//arch.AppendLinea(scanner.Bytes())
				lineas += 1
			}

			if lineas == 0 {
				panic("***** Sin lineas men")
			}

			//fmt.Printf("----- Contents of %s\n", f2.Name)

			arch.TotalLineas = lineas
			arch.ClasificarLineas()
			containerArch.AppendArchivo(*arch)

			numtxts += 1

			//fmt.Printf("----- Contents of %s\n", f2.Name)
			//fmt.Printf("***** Total de lineas %d\n", lineas)

		}

	}

	fmt.Printf("***** Total archivos text %d\n", numtxts)
}

func ToReader(buf *bytes.Buffer, rc *io.ReadCloser) (io.Reader, int) {
	buf.ReadFrom(*rc)
	//Transform the *bytes.Buffer to []Bytes
	bytesArray := buf.Bytes()
	return bytes.NewReader(bytesArray), len(bytesArray)
}

func descartarX(archivo string, lenfile int) bool {
	p := []int{6, 14} //Uno mas que en PHP porque en Go empieza en 0

	for _, pos := range p {
		defpos := lenfile - pos
		if archivo[defpos:defpos+1] == "A" {
			fmt.Printf("****UPS - SkIp *****\n")
			return true
		}
	}
	return false
}

func descartarPensionados(f *zip.File, lenfile int) bool {
	defpos := lenfile - 15

	if f.Name[defpos:defpos+2] == "IP" {
		moverArchivo(f)
		return true
	}
	return false
}

func moverArchivo(f *zip.File) {
	pensionados := Ruta + "pensionados"
	if err := os.Mkdir(pensionados, 0775); err != nil {
		if os.IsExist(err) {
			rc, err := f.Open()
			if err != nil {
				log.Fatal(err)
			}
			buf := new(bytes.Buffer)
			buf.ReadFrom(rc)
			file, err1 := os.Create(pensionados + "/" + f.Name)
			if err1 != nil {
				log.Fatal(err1)
			}
			file.Write(buf.Bytes())
			file.Close()
			rc.Close()
		} else {
			log.Fatal(err)
		}
	}

}

func main() {
	Prueba()
}
