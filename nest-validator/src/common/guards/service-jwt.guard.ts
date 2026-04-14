import {
  CanActivate,
  ExecutionContext,
  Injectable,
  UnauthorizedException,
} from '@nestjs/common';
import * as jwt from 'jsonwebtoken';

@Injectable()
export class ServiceJwtGuard implements CanActivate {
  canActivate(context: ExecutionContext): boolean {
    const request = context.switchToHttp().getRequest();
    const authHeader = request.headers.authorization;

    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      throw new UnauthorizedException('Missing service token');
    }

    const token = authHeader.substring(7);

    try {
      const payload = jwt.verify(
        token,
        process.env.SERVICE_JWT_SECRET as string,
      ) as {
        iss?: string;
        aud?: string;
        scope?: string;
      };

      if (payload.iss !== 'laravel-app') {
        throw new UnauthorizedException('Invalid issuer');
      }

      if (payload.aud !== 'nest-validator') {
        throw new UnauthorizedException('Invalid audience');
      }

      if (payload.scope !== 'internal-service') {
        throw new UnauthorizedException('Invalid scope');
      }

      request.serviceAuth = payload;

      return true;
    } catch {
      throw new UnauthorizedException('Invalid or expired service token');
    }
  }
}
