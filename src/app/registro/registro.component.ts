import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders, HttpErrorResponse } from '@angular/common/http';
import { catchError } from 'rxjs/operators';
import { throwError } from 'rxjs';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { AuthService } from '../auth.service'; 
import { RegisterService } from '../register.service'; 

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

  constructor(private authService:AuthService, private formBuilder: FormBuilder, private router: Router, private http: HttpClient, private registerService: RegisterService) { }

  get registerFormControl() {
    return this.registerForm.controls;
  }
  
  register() {
    console.log('hola')
    console
    const url = 'https://das-uabook.000webhostapp.com/register.php';
    const body = { 
        username: this.registerForm.get('username')?.value, 
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
              this.authService.setAuthenticated(true);
              this.router.navigate(['/inicio']);                  

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

}