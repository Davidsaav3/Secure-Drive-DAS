import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';

@Component({
  selector: 'app-register',
  templateUrl: 'register.component.html',
  styleUrls: ['./../app.component.css']
})
export class RegisterComponent {
  registerForm2: FormGroup = this.formBuilder.group({
    fa: ['', [Validators.required, Validators.minLength(6), Validators.maxLength(6)]],
  });
  registerForm: FormGroup = this.formBuilder.group({
    username: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(20)]],
    email: ['', [Validators.required, Validators.email]],
    password: ['', [Validators.required, Validators.minLength(6)]],
    confirmPassword: ['', [Validators.required, Validators.minLength(6)]]
  });

  mostrar: any= false;
  mostrar2: any= false;
  mostrar3: any= false;
  fa: string | undefined;

  constructor(private formBuilder: FormBuilder, private router: Router, private http: HttpClient) { }
  username = this.registerForm.get('username')?.value;
  email = this.registerForm.get('email')?.value;
  password = this.registerForm.get('password')?.value;
  confirmPassword = this.registerForm.get('confirmPassword')?.value;

  get registerFormControl() {
    return this.registerForm.controls;
  }
  
  register() {
    if (this.registerForm.valid) {
      console
      const url = 'https://proteccloud.000webhostapp.com/register.php';
      const body = { 
          username: this.username, 
          email: this.email, 
          confirmPassword: this.confirmPassword, 
          password: this.password 
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
              } else {
                  console.error(
                      `Código de error del servidor: ${error.status}, ` +
                      `cuerpo del error: ${error.error}`);
              }
              return throwError('Algo salió mal; inténtalo de nuevo más tarde.');
          })
      )
      .subscribe(
          (response: any) => {
              console.log('Respuesta:', response);
              if(response.code==100){
                this.mostrar= true;
              }
              if(response.code==400){
                this.mostrar2= true;
              }
          },
          (error: any) => {
              console.error('Error de solicitud:', error);
              // Aquí puedes realizar acciones adicionales en caso de error de solicitud
          }
      );
    }
    else{
      this.registerForm.markAllAsTouched();
    }
}

  comp() {
    if (this.registerForm2.valid) {
      console
      const url = 'https://proteccloud.000webhostapp.com/code.php';
      const body = { 
          fa: this.fa, 
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
              } else {
                  console.error(
                      `Código de error del servidor: ${error.status}, ` +
                      `cuerpo del error: ${error.error}`);
              }
              return throwError('Algo salió mal; inténtalo de nuevo más tarde.');
          })
      )
      .subscribe(
          (response: any) => {
              console.log('Respuesta:', response);
              if(response.code==100){
                this.router.navigate(['/home']);
              }
              if(response.code==400){
                this.mostrar3= true;
              }
          },
          (error: any) => {
              console.error('Error de solicitud:', error);
              // Aquí puedes realizar acciones adicionales en caso de error de solicitud
          }
      );
    }
    else{
      this.registerForm.markAllAsTouched();
    }
  }
}