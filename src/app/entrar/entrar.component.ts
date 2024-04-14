import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 
import { ActivatedRoute } from '@angular/router';

@Component({
  selector: 'app-entrar',
  templateUrl: './entrar.component.html',
  styleUrls: ['./../app.component.css']
})
export class EntrarComponent {

  registerForm2: FormGroup = this.formBuilder.group({
    fa: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(6)]],
  });
  registerForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(20)]],
    password: ['', [Validators.required, Validators.minLength(6), Validators.pattern(/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/)]]
  });

  cont: any= 0;
  mostrar: any= false;
  mostrar2: any= false;
  mostrar3: any= false;
  mostrar4: any= false;
  mostrar5: any= false;
  fa: string | undefined;
  username= '';

  constructor(private authService:AuthService, private formBuilder: FormBuilder, private router: Router, private http: HttpClient, private route: ActivatedRoute) { }

  get registerFormControl() {
    return this.registerForm.controls;
  }

  OnInit(){
    this.route.queryParams.subscribe(params => {
      const code = params['code'];
      if (code) {
        this.authService.sendAuthorizationCode(code).subscribe(
          (response) => {
            console.log('Token recibido del backend:', response);
            localStorage.setItem('username', response.username);
            localStorage.setItem('id', response.id);
            this.authService.setAuthenticated(true);
            localStorage.setItem('token', response.token);
            this.router.navigate(['/inicio']);
          },
          (error) => {
            console.error('Error al enviar el código de autorización al backend:', error);
          }
        );
      }
    });
  }

  login() {
    if (this.registerForm.valid) {
      console
      const url = 'https://das-uabook.000webhostapp.com/login.php';
      const body = { 
        username: this.registerForm.get('username')?.value, 
        password: this.registerForm.get('password')?.value 
      };
      const httpOptions = {
        headers: new HttpHeaders({
          'Content-Type': 'application/x-www-form-urlencoded'
        })
      };
      this.http.post(url, JSON.stringify(body), httpOptions)
      .pipe(
        catchError((error: HttpErrorResponse) => {
          if (error.error instanceof ErrorEvent) {
            console.error('Error del lado del cliente:', error.error.message);
          } 
          else {
            if(error.status==200){
              this.username = this.registerForm.get('username')?.value;
              this.mostrar= true;
            }
          }
          return throwError('Algo salió mal; inténtalo de nuevo más tarde.');
        })
      )
      .subscribe(
        (response: any) => {
          if(response.code==100 || response.code==200){
            this.username = this.registerForm.get('username')?.value;
            localStorage.setItem('username', response.username);
            localStorage.setItem('id', response.id);
            this.authService.setAuthenticated(true);
            this.router.navigate(['/inicio']);
            localStorage.setItem('token', response.token);
          }
          if(response.code==400){
            this.mostrar2= true;
          }
          if(response.code==401){
            this.mostrar4= true;
          }
        },
        (error: any) => {
          console.error('Error de solicitud:', error);
        }
      );
    }
    else{
      this.registerForm.markAllAsTouched();
    }
  }

  loginWithGoogle() {
    window.location.href = 'https://accounts.google.com/o/oauth2/v2/auth?' +
      'scope=email%20profile&' +
      'response_type=code&' +
      'redirect_uri=http://localhost:4200/inicio&' +
      'client_id=230938867099-dhnn6mvf1jvpnn9k74hhpmd9k96ajvo5.apps.googleusercontent.com';
  }

}