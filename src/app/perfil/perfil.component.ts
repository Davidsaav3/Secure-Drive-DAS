import { Component, OnInit } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 
import { Router, ActivatedRoute } from '@angular/router';
import { Pipe, PipeTransform } from '@angular/core';
import { DomSanitizer, SafeUrl } from '@angular/platform-browser';
import { Get_my_postsService } from '../get_my_posts.service';
import { Get_profileService } from '../get_profile.service';

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

  constructor( private get_profileService: Get_profileService,  private get_my_postsService : Get_my_postsService, private route: ActivatedRoute, private sanitizer: DomSanitizer, private authService:AuthService, private formBuilder: FormBuilder, private http: HttpClient, private router: Router) {
    this.profilename = this.route.snapshot.url[1].path;
  }
 
  files: any[] = [];
  fileName: string = '';
  email: any;
  pag= 0;

  username= localStorage.getItem('username');
  id= localStorage.getItem('id');
  id_post= 1;

  profilename: String | undefined;
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

  switchState: boolean = false; // Estado inicial del switch
  comentario: string = ''; // Propiedad para almacenar el valor del input

  shareForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required]],
  });

  user = {
    id: 0,
    username: "",
    following: 0,
    followers: 0,
    num_posts: 0,
    status: 0,
    requests: [
      {
        id_user: 0,
        username: ""
      }
    ],
  };

  posts = {
    post:[
      {
        id_post: -1,
        id_user: 0,
        text_post: "",
        url_image: "",
        date: "",
        likes: 0,
        username: "",
        comments: [
          {
            id: 0,
            username: "",
            text: ""
          }
        ],
      }
    ],
  };

  mostrarComentarios= false;

  postData = {
    text: '',
    image: null as File | null  // Se inicializa como null y se asignará al seleccionar un archivo
  };

  ngOnInit(): void { // INICILIZACIÓN
    this.getProfile()
    this.getMyPosts()

    if (localStorage.getItem('username')==null) {
      this.router.navigate(['entrar']);
    }
  }

  getProfile(){ // OBTIENE DATOS DE USUARIO
    console.log(this.profilename)
    this.get_profileService.get_profile(this.profilename).subscribe(
      (response: any) => {
        console.log(response)
        this.user = response;
      },
      (error: any) => {
        console.error('Error al obtener datos de usuario:', error);
      }
    );
  }

  getMyPosts() {  // OBTENER POSTS
    console.log(this.profilename)
    this.get_my_postsService.get_my_posts(this.profilename).subscribe(
      (response: any) => {
        console.log(response)
        this.posts.post = response;
      },
      (error: any) => {
        console.error('Error al obtener las publicaciones del usuario:', error);
      }
    );
  }  

  getProfilename(event: any) { // OBTENER NOMBRE DE USUARIO
    let input = event.target;
    this.fileName = input.files[0].name;
    setTimeout(() => {
      this.fileName= '';
      this.getMyPosts();
    }, 1000);
  }

  deletePost() { // ELIMINA UNA PUBLICACIÓN
    const token = localStorage.getItem('token');
    const headers = new HttpHeaders({
      'Content-Type': 'application/x-www-form-urlencoded',
    })
    
    const url = `https://das-uabook.000webhostapp.com/delete_post.php`;
    const body = { 
      id_post: this.id_post,
      id_user: this.id, 
      token: token
    };

  this.http.post(url, JSON.stringify(body), { headers: headers })
    .subscribe(
      (data: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getMyPosts();
          this.getProfile();
        }, 500);
      },
      (error: any) => {
        this.Okdeleteall = true;  
        setTimeout(() => {
          this.Okdeleteall =  false;
        }, 3000);
        setTimeout(() => {
          this.getMyPosts();
        }, 500);
      }
    );
  }
  
  postLike(id_post: any) { // DAR LIKE
    const token = localStorage.getItem('token');
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/post_like.php`;
    const body = { 
      id: id_post, 
      id_user: this.id, 
      token: token
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        setTimeout(() => {
          this.getMyPosts();
        }, 500);
      },
      (error: any) => {
        setTimeout(() => {
          this.getMyPosts();
        }, 500);
      }
    );
  }

  postComment(id_post: any, text: any){ // COMENTAR POST
    const token = localStorage.getItem('token');
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/post_comment.php`;
    const body = { 
      id_post: id_post, 
      id_user: this.id, 
      text: text,
      token: token
    };

  if(this.comentario!=''){
    this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        setTimeout(() => {
          this.getMyPosts();
          this.mostrarComentarios= true;
          this.comentario= '';
        }, 500);
      },
      (error: any) => {
        setTimeout(() => {
          this.getMyPosts();
        }, 500);
      }
    );
  }
  }

  editPost() { // EDITAR PERFIL
    const token = localStorage.getItem('token');
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/edit_post.php`;
    const body = { 
      id: this.id_post,
      text: this.postData.text, 
      token: token 
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        setTimeout(() => {
          this.getMyPosts();
        }, 500);
      },
      (error: any) => {
        setTimeout(() => {
          this.getMyPosts();
        }, 500);
      }
    );
  }

  onFileSelected(event: any) {
    const file: File = event.target.files[0];
    this.postData.image = file;
  }

  PostRequest(){ // ACEPTAR O DENEGAR SOLICITUD DE SEGUIMIENTO
    const token = localStorage.getItem('token');
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/post_request.php`;
    const body = { 
      id_sender: this.id,
      id_receiver: this.user.id,
      token: token
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        setTimeout(() => {
          this.getProfile();
        }, 500);
      },
      (error: any) => {
        setTimeout(() => {
          this.getProfile();
        }, 500);
      }
    );
  }

  ResolveRequest(state: any){ // RESOLVER SOLICITUD DE SEGUIMIENTO
    const token = localStorage.getItem('token');
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/resolve_request.php`;
    const body = { 
      id_sender: this.user.id,
      id_receiver: this.id,
      status: state,
      token: token
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        setTimeout(() => {
          this.getProfile();
        }, 500);
      },
      (error: any) => {
        setTimeout(() => {
          this.getProfile();
        }, 500);
      }
    );
  }

  editProfile(state: any){ // RESOLVER SOLICITUD DE SEGUIMIENTO
    const token = localStorage.getItem('token');
    const httpOptions = {
      headers: new HttpHeaders({
        'Content-Type': 'application/x-www-form-urlencoded'
      })
    };
    const url = `https://das-uabook.000webhostapp.com/edit_profile.php`;
    const body = { 
      id_user: this.id,
      status: state,
      token: token
    };

  this.http.post(url, JSON.stringify(body), httpOptions)
    .subscribe(
      (data: any) => {
        setTimeout(() => {
          //this.getProfile();
        }, 500);
      },
      (error: any) => {
        setTimeout(() => {
          //this.getProfile();
        }, 500);
      }
    );
  }


}