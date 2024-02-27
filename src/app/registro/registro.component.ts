import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 

@Component({
  selector: 'app-registro',
  templateUrl: 'registro.component.html',
  styleUrls: ['./../app.component.css']
})
export class RegistroComponent {
  registerForm2: FormGroup = this.formBuilder.group({
    fa: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(6)]],
  });
  registerForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(20)]],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(6)]],
    confirmPassword: ['', [Validators.required, Validators.minLength(6)]]
  });

  cont: any= false;
  mostrar: any= false;
  mostrar2: any= false;
  mostrar3: any= false;
  mostrar4: any= false;
  mostrar5: any= false;
  fa: string | undefined;
  username= '';

  constructor(private authService:AuthService, private formBuilder: FormBuilder, private router: Router, private http: HttpClient) { }

  get registerFormControl() {
    return this.registerForm.controls;
  }
  
  register() {
    if (this.registerForm.valid) {
      console
      const url = 'https://dasapp.alwaysdata.net/register';
      const body = { 
          username: this.registerForm.get('username')?.value, 
          email: this.registerForm.get('email')?.value, 
          confirmPassword: this.registerForm.get('confirmPassword')?.value, 
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
                //console.error('Error del lado del cliente:', error.error.message);
              } 
              else {
                if(error.status==200){
                  this.mostrar= true;
                  this.username = this.registerForm.get('username')?.value;
                }
              }
              return throwError('Algo salió mal; inténtalo de nuevo más tarde.');
          })
      )
      .subscribe(
          (response: any) => {
              if(response.code==100 || response.code==200){
                this.mostrar= true;
                this.username = this.registerForm.get('username')?.value;
              }
              if(response.code==400){
                this.mostrar2= true;
              }
              if(response.code==401){
                this.mostrar4= true;
              }
          },
          (error: any) => {
            //console.error('Error de solicitud:', error);              
          }
      );
    }
    else{
      this.registerForm.markAllAsTouched();
    }
}

  comp() {
    this.cont++;
    if (this.registerForm2.valid && this.cont<3) {
      console
      const url = 'https://dasapp.alwaysdata.net/code';
      const body = { 
        username: this.registerForm.get('username')?.value, 
        fa: this.registerForm2.get('fa')?.value
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
                  //console.error('Error del lado del cliente:', error.error.message);
              } else {
                  //console.error(`Código de error del servidor: ${error.status}, ` + `cuerpo del error: ${error.error}`);
                      if(error.status==200){
                        localStorage.setItem('username', this.username);
                        this.authService.setAuthenticated(true);
                        this.router.navigate(['/inicio']);
                      }
              }
              return throwError('Algo salió mal; inténtalo de nuevo más tarde.');
          })
      )
      .subscribe(
          (response: any) => {
              if(response.code==100){
                localStorage.setItem('username', this.username);
                this.authService.setAuthenticated(true);
                this.router.navigate(['/inicio']);
              }
              if(response.code==400){
                this.mostrar3= true;
              }
          },
          (error: any) => {
            //console.error('Error de solicitud:', error);
          }
      );
    }
    else{
      this.registerForm.markAllAsTouched();
    }
    if(this.cont>=3){
      this.mostrar5= true;
      this.mostrar3= false;
      setTimeout(() => {
        this.router.navigate(['/entrar']);
      }, 2000);
      this.cont= 0;
    }
  }
}