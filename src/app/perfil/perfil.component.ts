import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 
import { DownloaderService } from '../downloader.service';
import { DownloaderSharedService } from '../downloaderShared.service';
import { UploadService } from '../upload.service';
import { FilesService } from '../files.service';
import { MyfilesService } from '../myshare.service';
import { OtherfilesService } from '../othershare.service';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';
import { DownService } from '../down.service';
import { Router, ActivatedRoute } from '@angular/router';
import { Pipe, PipeTransform } from '@angular/core';

@Pipe({ name: 'replace' })
export class ReplacePipe implements PipeTransform {
  transform(value: string, find: string, replacement: string): string {
    return value.replace(new RegExp(find, 'g'), replacement);
  }
}

@Component({
  selector: 'app-perfil',
  templateUrl: './perfil.component.html',
  styleUrls: ['./../app.component.css']
})

export class PerfilComponent implements OnInit {

  constructor(private route: ActivatedRoute, private downloaderShared: DownloaderSharedService, private downService: DownService, private sanitizer: DomSanitizer, private filesService: FilesService,private uploadService: UploadService, private downloaderService: DownloaderService, private authService:AuthService, private formBuilder: FormBuilder, private http: HttpClient, private myfilesService: MyfilesService, private otherfilesService: OtherfilesService, private router: Router) {
  }
 
  files: any[] = [];
  fileName: string = '';
  email: any;
  pag= 0;
  username= localStorage.getItem('username');
  profilename: String | undefined;
  id= 'p2.txt';
  archivos: any[] = [];
  archivos2: any[] = [];
  fileList: any;
  options: string[] = [];

  profile= true;
  follow= true;
  numfollow= 0;
  numfollowers= 0;
  numimages= 0;
  megusta= 0;

  Okshare = false;
  Notshare = false;
  Okdelete = false;
  UserNotshare = false;
  Nodelete = false;
  Noupload = false;
  Okdeleteall = false;
  NoNoupload = false;
  Okupload = false;
  mostrarDiv: boolean = false;
  parametroDeUrl: any;
  imageName = 'tu-imagen.jpg'; // Reemplaza con el nombre de tu imagen
  imageUrl1: string[] = [];
  folderPath = '/imagenes'; // Reemplaza con la ruta de tus imágenes
  images: any[] = [];

  shareForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required]],
  });

  user = {
    id: 0,
    username: "davidsaav3",
    profilepicture:"https://via.placeholder.com/150", 
    email: "",
    password: "",
    following: 11,
    followers: 12,
    posts: 13,
    open: true,
    reqqests: [
      {
        id: 0,
        username: "Davidsaav3"
      },
      {
        id: 1,
        username: "Luis"
      },
    ],
  };

  posts = {
    post:[
      {
        id: 0,
        name: "imagen",
        likes: 10,
        url: "https://via.placeholder.com/600",
        comments: [
          {
            id: 0,
            username: "Davidsaav3",
            text: "Contenido de comentario"
          },
          {
            id: 1,
            username: "Luis",
            text: "Contenido de comentario"
          },
        ],
      },
      {
        id: 0,
        name: "imagen",
        likes: 10,
        url: "https://via.placeholder.com/600",
        comments: [
          {
            id: 0,
            username: "Davidsaav3",
            text: "Contenido de comentario"
          },
          {
            id: 1,
            username: "Luis",
            text: "Contenido de comentario"
          },
        ],
      },
  ],
  };

  ngOnInit(): void { // INICILIZACIÓN
    this.getData();
    this.login2();
    this.profilename = this.route.snapshot.url[1].path;
    if (localStorage.getItem('username')==null) {
      //this.router.navigate(['login']);
    }
    //this.getPosts();
  }

  toggleDiv() {
    this.mostrarDiv = !this.mostrarDiv;
  }

  getUser(){ // OBTIENE DATOS DE USUARIO
    const formData = new FormData();
    formData.append('username', this.username+``);
    return this.http.post<any>('https://dasapp.alwaysdata.net/select.php', formData)
      .subscribe(data => {
        if (data) {
          this.options = data.usernames;
        } 
        else {
          //console.error('Formato de respuesta incorrecto', data);
        }
      }, error => {
        //console.error('Error al realizar la solicitud', error);
      });  
  }

  getPosts() { // OBTENER IMAGENES DE USUARIO
    const folderPath = 'storage/' + this.username; 
    this.filesService.files(folderPath).subscribe(
      (response: any) => {
        this.archivos = response.archivos;
        for (let i = 0; i < this.archivos.length; i++) {
          const archivo = this.archivos[i];
          const filePath = this.username + '/' + archivo.nombre;
          this.downService.getFileView(filePath).subscribe(
            (url: string) => {
              this.archivos[i].url= url;
            },
            (error: any) => {
              //console.error('Error al obtener la imagen:', error);
            }
          );
        }
        //console.log(this.archivos)
      },
      (error: any) => {
        //console.error('Error al obtener la lista de archivos:', error);
      }
    );
  }  

  down(id :string): void { // DESCARGAR IMAGENES
    this.downService.getFileView(id).subscribe(
      (url: string) => {
        return url;
        this.imageUrl1.push(url);
      },
      (error: any) => {
        //console.error('Error al obtener la imagen:', error);
      }
    );
  }

  getPreImage(image: any): SafeUrl { // PREVISUALIZACIÓN IMAGENES
    const base64Image = image.contenido;
    const imageUrl = 'data:image/' + image.tipo + ';base64,' + base64Image;
    return this.sanitizer.bypassSecurityTrustUrl(imageUrl);
  }

  getUsername(event: any) { // OBTENER NOMBRE DE USUARIO ACTUAL
    let input = event.target;
    this.fileName = input.files[0].name;
    setTimeout(() => {
      this.fileName= '';
      this.getPosts();
    }, 1000);
  }

  detelePost(id: string) { // ELIMINA UNA PUBLICACIÓN
    const url = `https://dasapp.alwaysdata.net/files.php`;
    const httpOptions = {
      headers: new HttpHeaders({ 'Content-Type': 'application/json' })
    };
    const formData = new FormData();
    formData.append('file_name', this.username+'/'+id);

      const headers = new HttpHeaders();
      this.http.post('https://dasapp.alwaysdata.net/delete.php', formData, { headers })
        .subscribe(
          (data: any) => {
            this.Okdeleteall = true;  
            setTimeout(() => {
              this.Okdeleteall =  false;
            }, 3000);
            setTimeout(() => {
              this.getPosts();
            }, 500);
          },
          (error: any) => {
            this.Okdeleteall = true;  
            setTimeout(() => {
              this.Okdeleteall =  false;
            }, 3000);
            setTimeout(() => {
              this.getPosts();
            }, 500);
          }
        );
  }

  downloadPost(id: any) { // DESCARGAR IMAGEN
    this.downloaderService.downloader(this.username+'/'+id);
  }

  downloadPostExt(id: any, owner: any) { // DESCARGAR COMPARTIDA
    this.downloaderShared.downloader(owner+'/'+id, owner, this.username);
  }
  
  postLike(idUser: any, idPost: any) { // DAR LIKE
    const url = "https://dasapp.alwaysdata.net/postlike";
    const data = {
      idUser: 0,
      idPost: 0
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  postComment(idUser: any, idPost: any, text: any){ // COMENTAR POST
    const url = "https://dasapp.alwaysdata.net/postreqqest";
    const data = {
      idUser: 0,
      idPost: 0,
      text: "Contenido comentario"
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  editPost(idPost: any, name: any){ // EDITAR POST
    const url = "https://dasapp.alwaysdata.net/editpost";
    const data = {
      idPost: 0,
      name: "imagen"
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  resolveReqqest(idUser: any, idUser2: any){ // ACEPTAR O DENEGAR / SOLICCITUD
    const url = "https://dasapp.alwaysdata.net/resolvereqqest";
    const data = {
      idUser: 0,
      idUser2: 0
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  postRequest(idUser: any, idUser2: any){ // SEGUIR USUARIO
    const url = "https://dasapp.alwaysdata.net/postreqqest";
    const data = {
      idUser: 0,
      idUser2: 0
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }

  postPublicPrivateProfile(id: any, option: any){ // ACEPTAR SOLICITUD
    const url = "https://dasapp.alwaysdata.net/resolvereqqest";
    const data = {
      id: 0,
      option: true,
    };
    this.http.post(url, data).subscribe((respuesta) => {
      console.log("Respuesta:", respuesta);
    }, (error) => {
      console.log("Error:", error);
    });
  }
  
  login2() {
    const url = 'https://dasapp.alwaysdata.net/login';
    const credentials = { username: 'david', password: '123' };

    this.http.post<any>(url, credentials).subscribe(
      (response) => {
        console.log('Respuesta del servidor:', response);
      },
      (error) => {
        console.error('Error al iniciar sesión:', error);
      }
    );
  }
  
  getData() {
    const url = 'https://dasapp.alwaysdata.net/data';
    this.http.get<any>(url).subscribe(
      (response) => {
        console.log('Respuesta del servidor:', response);
      },
      (error) => {
        console.error('Error al obtener datos:', error);
      }
    );
  }

}