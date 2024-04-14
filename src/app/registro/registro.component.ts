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

  registerForm: FormGroup = new FormGroup({});
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

  ngOnInit(): void {
    this.registerForm = this.formBuilder.group({
      username: ['', [Validators.required, Validators.minLength(3), Validators.maxLength(20)]],
      password: ['', [Validators.required, Validators.minLength(6), Validators.pattern(/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/)]],
      confirmPassword: ['', [Validators.required, Validators.minLength(6), Validators.pattern(/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,}$/)]]
    }, {
      validators: this.passwordsMatchValidator
    });
  }

  passwordsMatchValidator(formGroup: FormGroup) {
    const passwordControl = formGroup.get('password');
    const confirmPasswordControl = formGroup.get('confirmPassword');
  
    if (passwordControl && confirmPasswordControl) { // Verificación de nulidad
      if (passwordControl.value !== confirmPasswordControl.value) {
        confirmPasswordControl.setErrors({ passwordsNotMatching: true });
      } 
      else {
        confirmPasswordControl.setErrors(null);
      }
    }
  }

  
  register() {
    if (this.registerForm.valid) {

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
                console.error('Error del lado del cliente:', error.error.message);
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
                localStorage.setItem('username', response.username);
                localStorage.setItem('id', response.id);
                this.authService.setAuthenticated(true);
                localStorage.setItem('token', response.token);
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
            console.error('Error de solicitud:', error);              
          }
      );
    }
    else{
      this.registerForm.markAllAsTouched();
    }
  }
  

}